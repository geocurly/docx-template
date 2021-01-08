<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Parser\ExpressionParser;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Tests\Common\NodeTrait;
use DocxTemplate\Tests\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Lexer\Parser\ExpressionParser
 */
class ExpressionParserTest extends TestCase
{
    use ReaderTrait;
    use NodeTrait;

    /**
     * @dataProvider positiveProvider
     *
     * @param string $content
     * @param Node $left
     * @param Node|null $expected
     * @throws InvalidSourceException
     * @throws SyntaxErrorException
     */
    public function testParsePositive(string $content, Node $left, ?Node $expected): void
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
                    self::str(10, 3, '`1`')
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
                            '`string ${var}`',
                            self::block(
                                26,
                                6,
                                '${var}',
                                self::id('var', 28, 3),
                            )
                        ),
                        self::block(
                            38,
                            6,
                            '${var}',
                            self::id('var', 40, 3)
                        )
                    )
                )
            ]
        ];
    }
}
