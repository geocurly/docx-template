<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Parser;

use DocxTemplate\Lexer\Parser\BlockParser;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Lexer\Parser\Exception\EndNotFoundException;
use DocxTemplate\Tests\Common\NodeTrait;
use DocxTemplate\Tests\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @covers \DocxTemplate\Lexer\Parser\BlockParser
 * @covers \DocxTemplate\Lexer\Parser\Parser
 */
class BlockParserTest extends TestCase
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
                (new BlockParser($reader, $pos))->parse(),
                "Try to find nested element in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            ['var', 0, null],
            ['${ var }', 0, self::block(0, 8,'${ var }', self::id('var', 3, 3))],
            [
                '${ image(`str`):150x150 | filter }',
                0,
                self::block(
                    0,
                    34,
                    '${ image(`str`):150x150 | filter }',
                    self::filter(
                        self::image(
                            self::call(
                                self::id('image', 3, 5),
                                3,
                                12,
                                self::str(9, 5, '`str`',)
                            ),
                            self::imageSize(16, 7, '150', '150')
                        ),
                        self::id('filter', 26, 6)
                    )
                )
            ],
            [
                '${ ${ if ? then : else } | filter }',
                0,
                self::block(
                    0,
                    35,
                    '${ ${ if ? then : else } | filter }',
                    self::filter(
                        self::block(
                            3,
                            21,
                            '${ if ? then : else }',
                            self::cond(
                                self::id('if', 6, 2),
                                self::id('then', 11, 4),
                                self::id('else', 18, 4)
                            )
                        ),
                        self::id('filter', 27, 6)
                    )
                )
            ],
            [
                '\${ escaped }',
                0,
                self::escapedBlock(
                    0,
                    13,
                    '\${ escaped }',
                    self::id('escaped', 4, 7)
                )
            ],
            [
                '${ var ? \${ escaped } : `` }',
                0,
                self::block(
                    0,
                    29,
                    '${ var ? \${ escaped } : `` }',
                    self::cond(
                        self::id('var', 3, 3),
                        self::escapedBlock(
                            9,
                            13,
                            '\${ escaped }',
                            self::id('escaped', 13, 7)
                        ),
                        self::str(25, 2, '``')
                    )
                )
            ],
            [
                '${ var foo }',
                0,
                self::block(
                    0,
                    12,
                    '${ var foo }',
                    self::id('var', 3, 3),
                    self::id('foo', 7, 3),
                )
            ]
        ];
    }

    /**
     * @dataProvider parseNegativeProvider
     *
     * @psalm-param class-string<Throwable> $expected
     * @psalm-param string $content
     *
     * @throws InvalidSourceException
     * @throws SyntaxErrorException
     */
    public function testParseNegative(string $expected, string $content): void
    {
        $this->expectException($expected);
        foreach ($this->reader($content) as $reader) {
            (new BlockParser($reader, 0))->parse();
        }
    }

    public function parseNegativeProvider(): array
    {
        return [
            [
                SyntaxErrorException::class,
                '${ if ?: else var }',
            ],
            [
                SyntaxErrorException::class,
                '${',
            ],
            [
                EndNotFoundException::class,
                '${ ${var} '
            ],
        ];
    }
}
