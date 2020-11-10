<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Parser\ConditionParser;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\AstNodeTrait;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class ConditionParserTest extends TestCase
{
    use ReaderTrait;
    use AstNodeTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\ConditionParser::parse
     * @dataProvider positiveProvider
     *
     * @param string $content
     * @param AstNode $if
     * @param AstNode|null $expected
     * @throws InvalidSourceException
     * @throws SyntaxError
     */
    public function testParsePositive(string $content, AstNode $if, ?AstNode $expected): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                $expected,
                (new ConditionParser($reader, $if))->parse(),
                "Try to find condition in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            [
                'ifThen ?: else }',
                $if = self::id('ifThen', 0, 6),
                self::cond($if, $if, self::id('else', 10, 4)),
            ],
            [
                'if ? then(`param`) | filter : ${var1 var2} }',
                $if = self::id('if', 0, 2),
                self::cond(
                    $if,
                    self::filter(
                        self::call(
                            self::id('then', 5, 4),
                            5,
                            13,
                            self::str(10, 7),
                        ),
                        self::id('filter', 21, 6)
                    ),
                    self::block(
                        30,
                        12,
                        false,
                        self::id('var1', 32, 4),
                        self::id('var2', 37, 4),
                    )
                ),
            ],
            [
                'ifThen ? image : 150x150 : else }',
                $if = self::id('ifThen', 0, 6),
                self::cond(
                    $if,
                    self::image(
                        self::id('image', 9, 5),
                        self::imageSize(17, 7, '150', '150')
                    ),
                    self::id('else', 27, 4)
                ),
            ],
            [
                'if ? if : else }',
                $if = self::id('if', 0, 2),
                self::cond(
                    $if,
                    self::id('if', 5, 2),
                    self::id('else', 10, 4)
                ),
            ],
            [
                '${docx?then:else}',
                $if = self::id('docx', 2, 4),
                self::cond(
                    $if,
                    self::id('then', 7, 4),
                    self::id('else', 12, 4)
                ),
            ],
            [
                'i<test-tag>f ?</test-tag> if : e<bold>ls</bold>e }',
                $if = self::id('if', 0, 12),
                self::cond(
                    $if,
                    self::id('if', 26, 2),
                    self::id('else', 31, 17)
                ),
            ],
            [
                '`if ${var}` ? `then ${    `ternary` ? bar : `else ${nested}`}` : ``',
                $if = self::str(
                    0,
                    11,
                    self::block(
                        4,
                        6,
                        false,
                        self::id('var', 6, 3)
                    )
                ),
                self::cond(
                    $if,
                    self::str(
                        14,
                        48,
                        self::block(
                            20,
                            41,
                            false,
                            self::cond(
                                self::str(26, 9),
                                self::id('bar', 38, 3),
                                self::str(
                                    44,
                                    16,
                                    self::block(
                                        50,
                                        9,
                                        false,
                                        self::id('nested', 52, 6)
                                    ),
                                )
                            )
                        ),
                    ),
                    self::str(65, 2)
                ),
            ]
        ];
    }
}
