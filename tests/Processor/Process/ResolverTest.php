<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Processor\Process;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Contract\Processor\BindFactory as Factory;
use DocxTemplate\Exception\Processor\NodeException;
use DocxTemplate\Processor\Process\Bind\Filter\DateFilter;
use DocxTemplate\Processor\Process\Bind\ImageBind;
use DocxTemplate\Processor\Process\Resolver;
use DocxTemplate\Processor\Source\ContentTypes;
use DocxTemplate\Processor\Source\Relations;
use DocxTemplate\Tests\Common\BindTrait;
use DocxTemplate\Tests\Common\ImageSourceTrait;
use DocxTemplate\Tests\Common\NodeTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \DocxTemplate\Processor\Process\Resolver
 *
 */
class ResolverTest extends TestCase
{
    use NodeTrait;
    use BindTrait;
    use ImageSourceTrait;

    private const TEST_VALUE_1 = 'value_1';

    /**
     * @dataProvider solveProvider
     *
     * @param Node $node
     * @param string $expected
     */
    public function testSolvePositive(Node $node, string $expected): void
    {
        $resolver = new Resolver($this->factory(), $this->relations());
        self::assertEquals(
            $expected,
            $resolver->solve($node)->getValue(),
            "Try to solve " . get_class($node) . " with value: $expected."
        );
    }

    public function testSolveNegative(): void
    {
        $resolver = new Resolver($this->factory(), $this->relations());
        self::expectException(NodeException::class);
        $resolver->solve(
            new class implements Node {

                public function getPosition(): NodePosition
                {
                    return new NodePosition(0, 0);
                }

                public function getType(): string
                {
                    return 'Stub';
                }

                public function toArray(): array
                {
                    return [];
                }
            }
        );
    }

    public function solveProvider(): array
    {
        return [
            $this->getSimpleBind(),
            $this->getCallableBind(),
            $this->getSimpleBlockBind(),
            $this->getFilterChain(),
            $this->getTrueCondition(),
            $this->getFalseCondition(),
            $this->getEscapedBlock(),
            $this->getEscapedChar(),
            $this->getImageWithSize(),
            $this->getImageWithoutSize(),
            $this->getEmptyImage(),
        ];
    }

    private function getEmptyImage(): array {
        return [
            self::image(
            // emp:100x50
                self::id('emp', 0, 5),
                self::imageSize(
                    4,
                    6,
                    '100',
                    '50'
                )
            ),
            '',
        ];
    }

    private function getImageWithSize(): array
    {
        return [
            self::image(
                // img:100x50
                self::id('img', 0, 3),
                self::imageSize(
                    4,
                    6,
                    '100',
                    '50'
                )
            ),
            self::imgXml('rId1', '100px', '50px'),
        ];
    }

    private function getImageWithoutSize(): array
    {
        return [
            self::id('image_id', 0, 8),
            self::imgXml('rId1', '100%', '22%'),
        ];
    }

    private function factory(): Factory
    {
        $img = realpath(__DIR__ . '/../../Fixture/Image/cat.jpeg');
        return self::mockBindFactory(
            [
                'var' => fn() => 'value_1',
                'join' => fn(...$params) => implode('', $params),
            ],
            [
                'img' => [fn() => $img],
                'image_id' => [fn() => $img, [[100, '%'], [22, '%'], false]],
                'emp' => [fn() => ''],
            ]
        );
    }

    private function relations(): Relations
    {
        $xml  = <<<XML
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>
        XML;

        return new Relations('document.xml.rels', $xml);
    }

    private function types(): ContentTypes
    {
        $xml  = <<<XML
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>
        XML;

        return new ContentTypes('[Content_Types].xml', $xml);
    }

    private function getSimpleBind(): array
    {
        return [self::id('var',0, 3), self::TEST_VALUE_1];
    }

    private function getCallableBind(): array
    {
        return [
            // sum(var, `str \``)
            self::call(
                self::id(
                    'join',
                    0,
                    4
                ),
                0,
                16,
                self::id('var', 5, 3),
                self::str(10, 8, '`str \``', self::escaped(7, 2, '\`'))
            ),
            self::TEST_VALUE_1 . 'str `'
        ];
    }

    private function getSimpleBlockBind(): array
    {
        return [
            self::block(
                0,
                8,
                '${ var }',
                self::id('var', 3, 3)
            ),
            self::TEST_VALUE_1
        ];
    }

    private function getFilterChain(): array
    {
        return [
            self::block(
                0,
                8,
                '${ `17.01.1993` | date(`d.m.Y`) | date(`d-m-Y`) }',
                self::filter(
                    self::filter(
                        self::str(3, 12, '`17.01.1993`'),
                        self::call(
                            self::id('date', 18, 4),
                            18,
                            13,
                            self::str(23, 7, '`d.m.Y`')
                        ),
                    ),
                    self::call(
                        self::id('date', 34, 4),
                        34,
                        13,
                        self::str(39, 7, '`d-m-Y`')
                    ),
                )
            ),
            '17-01-1993'
        ];
    }

    private function getTrueCondition(): array
    {
        return [
            // var ? var : ``
            self::cond(
                self::id('var', 0 ,3),
                self::id('var', 6 ,3),
                self::str(12, 2, '``')
            ),
            self::TEST_VALUE_1
        ];
    }

    private function getFalseCondition(): array
    {
        return [
            // `` ?: var
            self::cond(
                self::str(0, 2, '``'),
                self::str(0, 2, '``'),
                self::id('var', 6 ,3),
            ),
            self::TEST_VALUE_1
        ];
    }

    private function getEscapedBlock(): array
    {
        return [
            // \${ var }
            self::escapedBlock(
                0,
                9,
                '\\${ var }',
                self::id('var', 4, 3)
            ),
            '${ var }'
        ];
    }

    private function getEscapedChar(): array
    {
        return [
            // \`
            self::escaped(
                0,
                2,
                '\\`'
            ),
            '`'
        ];
    }
}
