<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Parser\Exception\UnexpectedCharactersException;
use DocxTemplate\Lexer\Parser\IdentityParser;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use DocxTemplate\Exception\Lexer\SyntaxError;
use DocxTemplate\Tests\Common\NodeTrait;
use DocxTemplate\Tests\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class IdentityParserTest extends TestCase
{
    use ReaderTrait;
    use NodeTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Parser\IdentityParser::parse
     * @dataProvider positiveProvider
     *
     * @param string $content
     * @param int $pos
     * @param Node|null $expected
     * @throws InvalidSourceException|SyntaxError
     */
    public function testParsePositive(string $content, int $pos, ?Node $expected): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                $expected,
                (new IdentityParser($reader, $pos))->parse(),
                "Try to find identity in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            [' simple-name }', 0, self::id('simple-name', 1, 11)],
            ["      simple-name \n}", 4, self::id('simple-name', 6, 11)],
            [" one_two | simple-name \n}", 0, self::id('one_two', 1, 7)],
            [" one_two | simple-name \n}", 10, self::id('simple-name', 11, 11),],
            ["<simple-variable> one_<bold>two</bold> <style>|", 0, self::id('one_two', 18, 20)],
            [
                ' call(var)',
                0,
                self::call(
                    self::id('call', 1, 4),
                    1,
                    9,
                    self::id('var', 6, 3)
                )
            ],
            [
                ' call(`1`, `2`) ',
                0,
                self::call(
                    self::id('call', 1, 4),
                    1,
                    14,
                    self::str(6, 3, '`1`'),
                    self::str(11, 3, '`2`')
                ),
            ],
            [
                ' call(var, `2`, ${var}, image:150x150) ',
                0,
                self::call(
                    self::id('call', 1, 4),
                    1, 37,
                    self::id('var', 6, 3),
                    self::str(11, 3, '`2`'),
                    self::block(16, 6, '${var}', self::id('var', 18, 3)),
                    self::image(
                        self::id('image', 24, 5),
                        self::imageSize(30, 7, '150', '150')
                    )
                ),
            ],
            [
                ' call(`nested ${nested}`, `simple`) ',
                0,
                self::call(
                    self::id('call', 1, 4),
                    1,
                    34,
                    self::str(
                        6,
                        18,
                        '`nested ${nested}`',
                        self::block(14, 9, '${nested}', self::id('nested', 16, 6))
                    ),
                    self::str(26, 8, '`simple`')
                ),
            ],
            ['${ \void }', 3, self::id('\void', 3, 5)],
            ['${ \\\s^123px }', 3, self::id('\\\s^123px', 3, 9)]
        ];
    }

    /**
     * @covers \DocxTemplate\Lexer\Parser\IdentityParser::parse
     *
     * @dataProvider negativeProvider
     *
     * @param string $content
     * @param int $pos
     * @param string $expected
     */
    public function testParseNegative(string $content, int $pos, string $expected): void
    {
        $this->expectException($expected);
        foreach ($this->reader($content) as $reader) {
            (new IdentityParser($reader, $pos))->parse();
        }
    }

    public function negativeProvider(): array
    {
        return [
            ['${identity', 3, EndNotFoundException::class],
            ['   ', 0, SyntaxError::class],
            ['${ {identity} }', 3, UnexpectedCharactersException::class],
            ['${ ident`ity }', 3, UnexpectedCharactersException::class],
        ];
    }
}
