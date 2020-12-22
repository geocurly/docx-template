<?php

namespace DocxTemplate\Tests\Lexer;

use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Lexer\Lexer;
use DocxTemplate\Tests\Lexer\Common\AstNodeTrait;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    use AstNodeTrait;

    /**
     * @dataProvider getRunProvider
     * @covers       \DocxTemplate\Lexer\Lexer::run
     * @param $content
     * @param array $blocks
     * @throws SyntaxError
     */
    public function testSimpleParse($content, array $blocks): void
    {
        $lexer = new Lexer($content);
        foreach ($lexer->run() as $num => $block) {
            self::assertEquals($blocks[$num] ?? null, $block);
        }
    }

    public function getRunProvider(): array
    {
        return [
            [
                <<<'XML'
                <document>
                    <text>
                        ${ if ? if : else }
                        ${ var | filter }
                    </text>
                    <text>
                        ${image:150x10}
                    </text>
                </document>
                XML,
                [
                    self::block(
                        30,
                        19,
                        self::cond(
                            self::id('if', 33, 2),
                            self::id('if', 38, 2),
                            self::id('else', 43, 4),
                        ),
                    ),
                    self::block(
                        58,
                        17,
                        self::filter(
                            self::id('var', 61, 3),
                            self::id('filter', 67, 6),
                        ),
                    ),
                    self::block(
                        107,
                        15,
                        self::image(
                            self::id('image', 109, 5),
                            self::imageSize(115, 6, '150', '10'),
                        ),
                    ),
                ]
            ],
        ];
    }
}
