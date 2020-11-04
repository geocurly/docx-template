<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\Image;
use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Ast\Parser\Exception\UnsupportedArgumentException;
use DocxTemplate\Lexer\Ast\Parser\ImageParser;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class ImageParserTest extends TestCase
{
    use ReaderTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\ImageParser::parse
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
                (new ImageParser($reader, $pos))->parse(),
                "Try to find image in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            [
                '${ image:150x140 }',
                3,
                new Image(
                    new Identity('image', $this->pos(3, 5)),
                    new ImageSize($this->pos(9, 7), '150', '140')
                ),
            ],
            [
                '${   image-var:height=140px:width=500px:ratio=false }',
                5,
                new Image(
                    new Identity('image-var', $this->pos(5, 9)),
                    new ImageSize($this->pos(15, 36), '500px', '140px', false),
                ),
            ],
            [
                '${ image }',
                3,
                new Identity('image', $this->pos(3, 5)),
            ]
        ];
    }

    /**
     * @dataProvider negativeProvider
     * @covers \DocxTemplate\Lexer\Ast\Parser\ImageParser::parse
     *
     * @param string $content
     * @param int $pos
     * @param string $expected
     */
    public function testParseNegative(string $content, int $pos, string $expected): void
    {
        $this->expectException($expected);
        foreach ($this->reader($content) as $reader) {
            (new ImageParser($reader, $pos))->parse();
        }
    }

    public function negativeProvider(): array
    {
        return [
            ['${ image(`param1`, `param2`):150x150 }', 3, UnsupportedArgumentException::class],
            ['${ image:150x150', 3, EndNotFoundException::class],
        ];
    }
}
