<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use DocxTemplate\Lexer\Token\Name;
use DocxTemplate\Lexer\Token\Position\TokenPosition;
use DocxTemplate\Lexer\Token\Scope;
use DocxTemplate\Lexer\Token\Str;
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
     * @throws Exception\InvalidSourceException
     * @throws Exception\SyntaxError
     */
    public function testName(string $content, int $position, TokenInterface $expected, string $message): void
    {
        foreach ($this->parser($content) as $parser) {
            $this->assertEquals($expected, $parser->name($position), "Try to $message.");
        }
    }

    public function nameDataProvider(): array
    {
        return [
            [
                ' simple-name }',
                0,
                new Name('simple-name', $this->pos(1, 11)),
                "find simple name in simple content"
            ],
            [
                "      simple-name \n}",
                 4,
                new Name('simple-name', $this->pos(6, 11)),
                "find simple name in simple content"
            ],
            [
                " one_two | simple-name \n}",
                0,
                new Name('one_two', $this->pos(1, 7)),
                "find simple name in simple content with filter"
            ],
            [
                "<simple-variable> one_<bold>two</bold> <style>| simple-name \n</style>}</simple-variable>",
                0,
                new Name('one_<bold>two</bold>', $this->pos(18, 20)),
                "find simple name in simple content with filter and tags"
            ],
        ];
    }

    /**
     * @covers       \DocxTemplate\Lexer\TokenParser::string
     *
     * @dataProvider stringDataProvider
     *
     * @param string $content
     * @param int $position
     * @param TokenInterface $expected
     * @param string $message
     * @throws Exception\InvalidSourceException
     * @throws Exception\SyntaxError
     */
    public function testString(string $content, int $position, TokenInterface $expected, string $message): void
    {
        foreach ($this->parser($content) as $parser) {
            $this->assertEquals($expected, $parser->string($position), "Try to $message.");
        }
    }

    public function stringDataProvider(): array
    {
        return [
            [
                '`string`',
                0,
                new Str('string', $this->pos(0, 8)),
                "find simple string in '`string`'"
            ],
            [
                ' `string` ',
                0,
                new Str('string', $this->pos(1, 8)),
                "find simple string in ' `string` '"
            ],
            [
                '${ ${zip} ? `there is a zip` : `there is not a zip` }',
                0,
                new Str('there is a zip', $this->pos(12, 16)),
                "find simple string in '\${ \${zip} ? `there is a zip` : `there is not a zip` }'"
            ],
            [
                '<zip>`there is a <bold>zip</bold>`</zip>',
                5,
                new Str('there is a <bold>zip</bold>', $this->pos(5, 29)),
                "find string in '<zip>`there is a <bold>zip</bold>`</zip>'"
            ],
            [
                '`string ${nested} string`',
                0,
                new Str(
                    'string ${nested} string',
                    $this->pos(0, 25),
                    new Scope(
                        'nested',
                        $this->pos(8, 9),
                        new Name('nested', $this->pos(10, 6))
                    )
                ),
                "find nested string in '`string \${nested} string`"
            ],
            [
                $content = '`string ${nested} string ${`<tag>${nested2</tag>34}</br>` zip}`',
                0,
                new Str(
                    'string ${nested} string ${`<tag>${nested2</tag>34}</br>` zip}',
                    $this->pos(0, 63),
                    new Scope(
                        'nested',
                        $this->pos(8, 9),
                        new Name('nested', $this->pos(10, 6))
                    ),
                    new Scope(
                        '`<tag>${nested2</tag>34}</br>` zip',
                        $this->pos(25, 37),
                        new Str(
                            '<tag>${nested2</tag>34}</br>',
                            $this->pos(27, 30),
                            new Scope(
                                'nested2</tag>34',
                                $this->pos(33, 18),
                                new Name('nested2</tag>34', $this->pos(35, 15))
                            ),
                        ),
                        new Name('zip', $this->pos(58, 3))
                    )
                ),
                "find nested string in $content"
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

    private function pos(int $start, int $length): TokenPosition
    {
        return new TokenPosition(self::SOURCE_NAME, $start, $length);
    }
}
