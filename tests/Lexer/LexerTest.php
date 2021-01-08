<?php

namespace DocxTemplate\Tests\Lexer;

use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Lexer\Lexer;
use DocxTemplate\Tests\Common\NodeTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Lexer\Lexer
 */
class LexerTest extends TestCase
{
    use NodeTrait;

    /**
     * @dataProvider getRunProvider
     * @param $content
     * @param array $blocks
     * @throws SyntaxErrorException
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
                        '${ if ? if : else }',
                        self::cond(
                            self::id('if', 33, 2),
                            self::id('if', 38, 2),
                            self::id('else', 43, 4),
                        ),
                    ),
                    self::block(
                        58,
                        17,
                        '${ var | filter }',
                        self::filter(
                            self::id('var', 61, 3),
                            self::id('filter', 67, 6),
                        ),
                    ),
                    self::block(
                        107,
                        15,
                        '${image:150x10}',
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
