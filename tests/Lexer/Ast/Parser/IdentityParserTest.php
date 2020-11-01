<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Identity;
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
        ];
    }
}
