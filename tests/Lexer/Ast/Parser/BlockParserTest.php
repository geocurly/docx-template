<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Parser\BlockParser;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\AstNodeTrait;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class BlockParserTest extends TestCase
{
    use ReaderTrait;
    use AstNodeTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\BlockParser::parse
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
                (new BlockParser($reader, $pos))->parse(),
                "Try to find nested element in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            ['${ var }', 0, self::block(0, 8, self::id('var', 3, 3))],
            [
                '${ image(`str`):150x150 | filter }',
                0,
                self::block(
                    0,
                    34,
                    self::filter(
                        self::image(
                            self::call(
                                self::id('image', 3, 5),
                                3,
                                12,
                                self::str(9, 5)
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
                    self::filter(
                        self::block(
                            3,
                            21,
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
                    self::id('escaped', 4, 7)
                )
            ],
            [
                '${ var ? \${ escaped } : `` }',
                0,
                self::block(
                    0,
                    29,
                    self::cond(
                        self::id('var', 3, 3),
                        self::escapedBlock(
                            9,
                            13,
                            self::id('escaped', 13, 7)
                        ),
                        self::str(25, 2)
                    )
                )
            ],
        ];
    }
}
