<?php

namespace DocxTemplate\Tests\Ast;

use DocxTemplate\Ast\Ast;
use DocxTemplate\Ast\Node\Block;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Lexer\Reader\StringReader;
use DocxTemplate\Tests\Common\NodeTrait;
use PHPUnit\Framework\TestCase;


/** @covers \DocxTemplate\Ast\Ast */
class AstTest extends TestCase
{
    use NodeTrait;

    /**
     * @dataProvider getIteratorProvider
     *
     * @param string $content
     * @param array $expected
     * @throws SyntaxErrorException
     */
    public function testGetIteratorAggregate(string $content, array $expected): void
    {
        $ast = new Ast(new StringReader($content));
        $blocks = [];
        /** @var Block $block */
        foreach ($ast as $block) {
            $blocks[] = $block;
        }

        self::assertTrue($this->isObjectsSame($expected, $blocks));
    }

    private function isObjectsSame(array $first, array $second): bool
    {
        if (count($first) !== count($second)) {
            return false;
        }

        foreach ($first as $key => $object) {
            if (!isset($second[$key])) {
                return false;
            }

            if ($object != $second[$key]) {
                return false;
            }
        }

        return true;
    }

    public function getIteratorProvider(): array
    {
        return [
            [
                '${ var } external ${ foo } ${ bar }',
                [
                    self::block(0, 8, '${ var }', self::id('var', 3, 3)),
                    self::block(18, 8, '${ foo }', self::id('foo', 21, 3)),
                    self::block(27, 8, '${ bar }', self::id('bar', 30, 3)),
                ],
            ],
        ];
    }
}
