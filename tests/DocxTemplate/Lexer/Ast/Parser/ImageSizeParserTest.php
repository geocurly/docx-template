<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class ImageSizeParserTest extends TestCase
{
    /**
     * @dataProvider positiveProvider
     *
     * @covers \DocxTemplate\Lexer\Ast\Parser\ImageSizeParser
     * @covers \DocxTemplate\Lexer\Ast\Node\ImageSize
     *
     * @param string $content
     * @param ImageSize|null $size
     * @param int $pos
     * @throws InvalidSourceException
     * @throws SyntaxError
     */
    public function testParsePositive(string $content, int $pos, ?ImageSize $size): void
    {
        foreach (self::reader($content) as $reader) {
            self::assertEquals(
                $size,
                (new ImageSizeParser($reader, $pos))->parse(),
                "Try to find image size in \"$content\"."
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            [
                '150x200 ',
                0,
                new ImageSize(self::pos(0, 7), '150', '200'),
            ],
            [
                '150x200:t ',
                0,
                new ImageSize(self::pos(0, 9), '150', '200', true),
            ],
            [
                '2pxx200cm:f }',
                0,
                new ImageSize(self::pos(0, 11), '2px', '200cm', false),
            ],
            [
                'size=100%x300px }',
                0,
                new ImageSize(self::pos(0, 15), '100%', '300px'),
            ],
            [
                'size=999mm:333em }',
                0,
                new ImageSize(self::pos(0, 16), '999mm', '333em'),
            ],
            [
                'width=1px:height=3:ratio=true ',
                0,
                new ImageSize(self::pos(0, 29), '1px', '3', true),
            ],
            [
                ' width=123:height=123:ratio=t}',
                1,
                new ImageSize(self::pos(1, 28), '123', '123', true),
            ],
            [
                'width=123em:height=123px}',
                0,
                new ImageSize(self::pos(0, 24), '123em', '123px'),
            ],
            [
                'width=123:ratio=false:height=132 ',
                0,
                new ImageSize(self::pos(0, 32), '123', '132', false),
            ],
            [
                'width=123:ratio=true:height=132px}',
                0,
                new ImageSize(self::pos(0, 33), '123', '132px', true),
            ],
            [
                'height=1px:width=2em:ratio=t ',
                0,
                new ImageSize(self::pos(0, 28), '2em', '1px', true),
            ],
            [
                'height=2%:width=1%:ratio=false ',
                0,
                new ImageSize(self::pos(0, 30), '1%', '2%', false),
            ],
            [
                'height=2%:width=1% ',
                0,
                new ImageSize(self::pos(0, 18), '1%', '2%'),
            ],
            [
                'ratio=t:width=150:height=150 ',
                0,
                new ImageSize(self::pos(0, 28), '150', '150', true),
            ],
            [
                'ratio=f:height=777pt:width=9999in }',
                0,
                new ImageSize(self::pos(0, 33), '9999in', '777pt', false),
            ],
        ];
    }

    /**
     * @dataProvider negativeProvider
     *
     * @covers \DocxTemplate\Lexer\Ast\Parser\ImageSizeParser
     *
     * @param string $content
     * @param int $pos
     * @param string $exception
     * @throws InvalidSourceException
     * @throws SyntaxError
     */
    public function testParseNegative(string $content, int $pos, string $exception): void
    {
        $this->expectException($exception);
        foreach (self::reader($content) as $reader) {
            (new ImageSizeParser($reader, $pos))->parse();
        }
    }

    public function negativeProvider(): array
    {
        return [
            ['${ image:150ufx792 }', 9, SyntaxError::class],
            ['${ image:bad-image-size }', 9, SyntaxError::class],
            ['${ image:width=150px:width=560:height=29 }', 9, SyntaxError::class],
            ['${ image:ratio=f:width=1px:height=2:ratio=true }', 9, SyntaxError::class],
        ];
    }

    /**
     * @param string $content
     * @return ReaderInterface[]
     * @throws InvalidSourceException
     */
    protected static function reader(string $content): iterable
    {
        yield new StreamReader(stream_for($content));
        yield new StringReader($content);
    }

    /**
     * Make a node position mock
     *
     * @param int $start
     * @param int $length
     * @return NodePosition
     */
    protected static function pos(int $start, int $length): NodePosition
    {
        return new NodePosition($start, $length);
    }
}