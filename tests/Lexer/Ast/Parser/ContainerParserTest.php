<?php

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Lexer\Parser\ContainerParser;
use DocxTemplate\Tests\Common\NodeTrait;
use DocxTemplate\Tests\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

/** @covers \DocxTemplate\Lexer\Parser\ContainerParser */
class ContainerParserTest extends TestCase
{
    use ReaderTrait;
    use NodeTrait;

    /**
     * @dataProvider parsePositiveProvider
     *
     * @param string $content
     * @param Node|null $expected
     * @param int $offset
     * @throws InvalidSourceException
     * @throws SyntaxErrorException
     */
    public function testParsePositive(string $content, ?Node $expected, int $offset): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                $expected,
                (new ContainerParser($reader, $offset))->parse(),
                "Try to find container in '$content'. " . get_class($reader)
            );
        }
    }

    public function parsePositiveProvider(): array
    {
        return [
            [
                '${ block }',
                self::block(0, 10, '${ block }', self::id('block', 3, 5)),
                0,
            ],
            [
                '${ `string` }',
                self::str(
                    3,
                    8,
                    '`string`'
                ),
                2,
            ],
            [
                '\\${var}',
                self::escapedBlock(
                    0,
                    7,
                    '\\${var}',
                    self::id('var', 3, 3)
                ),
                0,
            ],
            [
                '\\something',
                null,
                0
            ]
        ];
    }
}
