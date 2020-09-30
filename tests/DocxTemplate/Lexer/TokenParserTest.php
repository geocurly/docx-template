<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use DocxTemplate\Lexer\Token\Name;
use DocxTemplate\Lexer\Token\Position\TokenPosition;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class TokenParserTest extends TestCase
{
    private const SOURCE_NAME = 'source';

    /**
     * @covers       \DocxTemplate\Lexer\TokenParser::name
     * @dataProvider nameDataProvider
     *
     * @param string $content
     * @param int $position
     * @param TokenInterface $expected
     * @param string $message
     * @throws Exception\InvalidSourceException|Exception\SyntaxError
     */
    public function testName(string $content, int $position, TokenInterface $expected, string $message): void
    {
        foreach ($this->parser($content) as $parser) {
            $this->assertEquals($expected, $parser->name($position), "Try to $message.");
        }
    }

    public function nameDataProvider(): array
    {
        $position = function (int $start, int $length) {
            return new TokenPosition(self::SOURCE_NAME, $start, $length);
        };

        return [
            [
                ' simple-name }',
                0,
                new Name('simple-name', $position(1, 11)),
                "find simple name in simple content"
            ],
            [
                "      simple-name \n}",
                4,
                new Name('simple-name', $position(6, 11)),
                "find simple name in simple content"
            ],
            [
                " one_two | simple-name \n}",
                0,
                new Name('one_two', $position(1, 7)),
                "find simple name in simple content with filter"
            ],
            [
                "<simple-variable> one_<bold>two</bold> <style>| simple-name \n</style>}</simple-variable>",
                0,
                new Name('one_two', $position(18, 20)),
                "find simple name in simple content with filter and tags"
            ],
        ];
    }

    /**
     * @param string $content
     * @return TokenParser[]
     * @throws Exception\InvalidSourceException
     */
    private function parser(string $content): iterable
    {
        yield new TokenParser(self::SOURCE_NAME, new StreamReader(stream_for($content)));
        yield new TokenParser(self::SOURCE_NAME, new StringReader($content));
    }
}
