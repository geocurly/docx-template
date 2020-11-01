<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\Identity;
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
        return [
            [
                ' simple-name }',
                0,
                new Identity('simple-name', $this->pos(1, 11)),
            ],
            [
                "      simple-name \n}",
                4,
                new Identity('simple-name', $this->pos(6, 11)),
            ],
            [
                " one_two | simple-name \n}",
                0,
                new Identity('one_two', $this->pos(1, 7)),
            ],
            [
                " one_two | simple-name \n}",
                10,
                new Identity('simple-name', $this->pos(11, 11)),
            ],
            [
                "<simple-variable> one_<bold>two</bold> <style>| simple-name \n</style>}</simple-variable>",
                0,
                new Identity('one_two', $this->pos(18, 20)),
            ],
            [
                ' call(`1`, `2`) ',
                0,
                new Call(
                    new Identity('call', $this->pos(1, 4)),
                    $this->pos(1, 14),
                    new Str($this->pos(6, 3)),
                    new Str($this->pos(11, 3))
                ),
            ],
            [
                ' call(`nested ${nested}`, `simple`) ',
                0,
                new Call(
                    new Identity('call', $this->pos(1, 4)),
                    $this->pos(1, 34),
                    new Str(
                        $this->pos(6, 18),
                        new Block(
                            $this->pos(14, 9),
                            new Identity('nested', $this->pos(16, 6))
                        )
                    ),
                    new Str($this->pos(26, 8))
                ),
            ]
        ];
    }
}
