<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\Str;
use DocxTemplate\Lexer\Ast\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Ast\Parser\StrParser;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class StrParserTest extends TestCase
{
    use ReaderTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\StrParser::image
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
        $str = function ($from, $length, ...$nested) {
            return new Str($this->pos($from, $length), ...$nested);
        };

        return [
            ['${ `string` }', 2, $str(3, 8)],
            [
                '<zip>`there is a <bold>zip</bold>`</zip>',
                0,
                $str(5, 29)
            ],
            [
                '`string ${nested} string`',
                0,
                $str(
                    0,
                    25,
                    new Block(
                        $this->pos(8, 9),
                        new Identity('nested', $this->pos(10, 6))
                    )
                ),
            ],
            [
                $content = '`string ${nested} string ${`<tag>${nested2</tag>34}</br>` zip}`',
                0,
                $str(
                    0,
                    63,
                    new Block(
                        $this->pos(8, 9),
                        new Identity('nested', $this->pos(10, 6))
                    ),
                    new Block(
                        $this->pos(25, 37),
                        $str(
                            27,
                            30,
                            new Block(
                                $this->pos(33, 18),
                                new Identity('nested234', $this->pos(35, 15))
                            ),
                        ),
                        new Identity('zip', $this->pos(58, 3))
                    )
                ),
            ],
        ];
    }

    /**
     * @dataProvider negativeProvider
     * @covers \DocxTemplate\Lexer\Ast\Parser\StrParser::image
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
