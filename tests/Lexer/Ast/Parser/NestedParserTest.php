<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Parser\NestedParser;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use DocxTemplate\Exception\Lexer\SyntaxError;
use DocxTemplate\Tests\Common\NodeTrait;
use DocxTemplate\Tests\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class NestedParserTest extends TestCase
{
    use ReaderTrait;
    use NodeTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Parser\NestedParser::parse
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
                (new NestedParser($reader, $pos))->parse(),
                "Try to find nested element in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            ['${ ${ var } }', 2, self::block(3, 8, '${ var }', self::id('var', 6, 3))],
            ['${ `str` } }', 2, self::str(3, 5, '`str`')],
            ['${ str } }', 2, self::id('str', 3, 3)],
            [
                '${ img:150x150 } }',
                2,
                self::image(
                    self::id('img', 3, 3),
                    self::imageSize(7, 7, '150', '150')
                )
            ],
            [
                '${ var | filter1 | filter2 }',
                2,
                self::filter(
                    self::filter(
                        self::id('var', 3, 3),
                        self::id('filter1', 9, 7)
                    ),
                    self::id('filter2', 19, 7)
                )
            ]
        ];
    }
}
