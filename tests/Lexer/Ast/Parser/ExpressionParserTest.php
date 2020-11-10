<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Parser\ExpressionParser;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\AstNodeTrait;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class ExpressionParserTest extends TestCase
{
    use ReaderTrait;
    use AstNodeTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\ExpressionParser::parse
     * @dataProvider positiveProvider
     *
     * @param string $content
     * @param AstNode $left
     * @param AstNode|null $expected
     * @throws InvalidSourceException
     * @throws SyntaxError
     */
    public function testParsePositive(string $content, AstNode $left, ?AstNode $expected): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                $expected,
                (new ExpressionParser($reader, $left))->parse(),
                "Try to find expression in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            [
                ' ${ var | filter } ',
                self::id('var', 4, 3),
                self::filter(self::id('var', 4, 3), self::id('filter', 10, 6))
            ],
            [
                '${ target(`1`) | filter }',
                $call = self::call(
                    self::id('target', 3, 6),
                    3,
                    11,
                    self::str(10, 3)
                ),
                self::filter($call, self::id('filter', 17, 6))
            ],
            [
                ' ${ var | filter( `string ${var}`  ,  ${var} )}',
                self::id('var', 4, 3),
                self::filter(
                    self::id('var', 4, 3),
                    self::call(
                        self::id('filter', 10, 6),
                        10,
                        36,
                        self::str(
                            18,
                            15,
                            self::block(
                                26,
                                6,
                                false,
                                self::id('var', 28, 3),
                            )
                        ),
                        self::block(
                            38,
                            6,
                            false,
                            self::id('var', 40, 3)
                        )
                    )
                )
            ]
        ];
    }
}
