<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Processor\Process;

use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Contract\Processor\BindFactory as Factory;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Processor\Process\Bind\Filter\Date;
use DocxTemplate\Processor\Process\Resolver;
use DocxTemplate\Tests\Common\BindTrait;
use DocxTemplate\Tests\Common\NodeTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Process\Resolver
 *
 * @uses \DocxTemplate\Processor\Process\Process
 */
class ResolverTest extends TestCase
{
    use NodeTrait;
    use BindTrait;

    private const TEST_VALUE_1 = 'value_1';

    /**
     * @dataProvider solveProvider
     *
     * @param Node $node
     * @param string $expected
     * @throws TemplateException
     */
    public function testSolve(Node $node, string $expected): void
    {
        $resolver = new Resolver($this->factory());
        self::assertEquals(
            $expected,
            $resolver->solve($node),
            "Try to solve " . get_class($node) . " with value: $expected."
        );
    }

    public function solveProvider(): array
    {
        return [
            $this->getSimpleBind(),
            $this->getCallableBind(),
            $this->getSimpleBlockBind(),
            $this->getFilterChain(),
        ];
    }

    private function factory(): Factory
    {
        return new class implements Factory {
            use BindTrait;

            public function valuable(string $name): Valuable
            {
                switch ($name) {
                    case 'var':
                        return self::valuableBind('var', fn() => 'value_1');
                    case 'join':
                        return self::valuableBind('join', fn(...$params) => implode('', $params));
                    default:
                        throw new \RuntimeException();
                }
            }

            public function filter(string $name): Filter
            {
                switch ($name) {
                    case 'date':
                        return new Date();
                    default:
                        throw new \RuntimeException();
                }
            }
        };
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
}
