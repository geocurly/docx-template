<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Parser;

use DocxTemplate\Lexer\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Parser\StrParser;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Tests\Common\NodeTrait;
use DocxTemplate\Tests\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Lexer\Parser\StrParser
 * @covers \DocxTemplate\Lexer\Parser\Parser
 */
class StrParserTest extends TestCase
{
    use ReaderTrait;
    use NodeTrait;

    /**
     * @dataProvider positiveProvider
     *
     * @param string $content
     * @param int $pos
     * @param Node|null $expected
     * @throws InvalidSourceException|SyntaxErrorException
     */
    public function testParsePositive(string $content, int $pos, ?Node $expected): void
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
            ['${ `string` }', 2, self::str(3, 8, '`string`')],
            [
                '${ `str \${ escaped }` }',
                2,
                self::str(
                    3,
                    19,
                    '`str \${ escaped }`',
                    self::escapedBlock(
                        8,
                        13,
                        '\${ escaped }',
                        self::id('escaped', 12, 7)
                    )
                )
            ],
            [
                '${ `test \`escaped\` string` }',
                2,
                self::str(
                    3,
                    25,
                    '`test \`escaped\` string`',
                    self::escaped(9, 2, '\`'),
                    self::escaped(18, 2, '\`'),
                )
            ],
            [
                '<zip>`there is a <bold>zip</bold>`</zip>',
                0,
                self::str(5, 29, '`there is a zip`')
            ],
            [
                '`string ${nested} string`',
                0,
                self::str(
                    0,
                    25,
                    '`string ${nested} string`',
                    self::block(8, 9, '${nested}', self::id('nested', 10, 6))
                ),
            ],
            [
                '`string ${nested} string ${`<tag>${nested2</tag>34}</br>` zip}`',
                0,
                self::str(
                    0,
                    63,
                    '`string ${nested} string ${`${nested234}` zip}`',
                    self::block(8, 9, '${nested}', self::id('nested', 10, 6)),
                    self::block(
                        25,
                        37,
                        '${`${nested234}` zip}',
                        self::str(
                            27,
                            30,
                            '`${nested234}`',
                            self::block(
                                33,
                                18,
                                '${nested234}',
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
