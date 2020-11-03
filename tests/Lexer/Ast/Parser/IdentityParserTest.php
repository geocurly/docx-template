<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\Image;
use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\Node\Str;
use DocxTemplate\Lexer\Ast\Parser\IdentityParser;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class IdentityParserTest extends TestCase
{
    use ReaderTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\IdentityParser::image
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
                (new IdentityParser($reader, $pos))->parse(),
                "Try to find identity in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        $id = function ($name, $from, $length) {
            return new Identity($name, $this->pos($from, $length));
        };

        return [
            [' simple-name }', 0, $id('simple-name', 1, 11)],
            ["      simple-name \n}", 4, $id('simple-name', 6, 11)],
            [" one_two | simple-name \n}", 0, $id('one_two', 1, 7)],
            [" one_two | simple-name \n}", 10, $id('simple-name', 11, 11),],
            ["<simple-variable> one_<bold>two</bold> <style>|", 0, $id('one_two', 18, 20)],
            [
                ' call(`1`, `2`) ',
                0,
                new Call(
                    $id('call', 1, 4),
                    $this->pos(1, 14),
                    new Str($this->pos(6, 3)),
                    new Str($this->pos(11, 3))
                ),
            ],
            [
                ' call(var, `2`, ${var}, image:150x150) ',
                0,
                new Call(
                    $id('call', 1, 4),
                    $this->pos(1, 37),
                    $id('var', 6, 3),
                    new Str($this->pos(11, 3)),
                    new Block(
                        $this->pos(16, 6),
                        $id('var', 18, 3)
                    ),
                    new Image(
                        $id('image', 24, 5),
                        new ImageSize($this->pos(30, 7), '150', '150')
                    )
                ),
            ],
            [
                ' call(`nested ${nested}`, `simple`) ',
                0,
                new Call(
                    $id('call', 1, 4),
                    $this->pos(1, 34),
                    new Str(
                        $this->pos(6, 18),
                        new Block(
                            $this->pos(14, 9),
                            $id('nested', 16, 6)
                        )
                    ),
                    new Str($this->pos(26, 8))
                ),
            ]
        ];
    }
}
