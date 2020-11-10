<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Ast\Parser\StrParser;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\AstNodeTrait;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class StrParserTest extends TestCase
{
    use ReaderTrait;
    use AstNodeTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\StrParser::parse
     * @dataProvider positiveProvider
     *
     * @param string $content
     * @param int $pos
     * @param AstNode|null $expected
     * @throws InvalidSourceException|SyntaxError
     */
    public function testParsePositive(string $content, int $pos, ?AstNode $expected): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                $expected,
                (new StrParser($reader, $pos))->parse(),
                "Try to find str in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            ['${ `string` }', 2, self::str(3, 8)],
            [
                '${ `test \`escaped\` string` }',
                2,
                self::str(
                    3,
                    25,
                    self::escaped(9, 2),
                    self::escaped(18, 2),
                )
            ],
            [
                '<zip>`there is a <bold>zip</bold>`</zip>',
                0,
                self::str(5, 29)
            ],
            [
                '`string ${nested} string`',
                0,
                self::str(
                    0,
                    25,
                    self::block(8, 9, false, self::id('nested', 10, 6))
                ),
            ],
            [
                '`string ${nested} string ${`<tag>${nested2</tag>34}</br>` zip}`',
                0,
                self::str(
                    0,
                    63,
                    self::block(8, 9, false, self::id('nested', 10, 6)),
                    self::block(
                        25,
                        37,
                        false,
                        self::str(
                            27,
                            30,
                            self::block(
                                33,
                                18,
                                false,
                                self::id(
                                    'nested234',
                                    35,
                                    15
                                )
                            )
                        ),
                        self::id('zip', 58, 3)
                    ),
                ),
            ],
        ];
    }

    /**
     * @dataProvider negativeProvider
     * @covers \DocxTemplate\Lexer\Ast\Parser\StrParser::parse
     *
     * @param string $content
     * @param int $pos
     * @param string $expected
     */
    public function testParseNegative(string $content, int $pos, string $expected): void
    {
        $this->expectException($expected);
        foreach ($this->reader($content) as $reader) {
            (new StrParser($reader, $pos))->parse();
        }
    }

    public function negativeProvider(): array
    {
        return [
            ['`something', 0, EndNotFoundException::class],
        ];
    }
}
