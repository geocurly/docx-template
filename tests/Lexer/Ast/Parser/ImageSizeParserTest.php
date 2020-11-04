<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Ast\Parser\Exception\InvalidImageSizeException;
use DocxTemplate\Lexer\Ast\Parser\ImageSizeParser;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\AstNodeTrait;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class ImageSizeParserTest extends TestCase
{
    use ReaderTrait;
    use AstNodeTrait;

    /**
     * @dataProvider positiveProvider
     *
     * @covers       \DocxTemplate\Lexer\Ast\Parser\ImageSizeParser::parse
     *
     * @param string $content
     * @param Identity $id
     * @param ImageSize|null $size
     * @throws InvalidSourceException
     * @throws SyntaxError
     */
    public function testParsePositive(string $content, Identity $id, ?ImageSize $size): void
    {
        foreach (self::reader($content) as $reader) {
            self::assertEquals(
                $size,
                (new ImageSizeParser($reader, $id))->parse(),
                "Try to find image size in \"$content\"."
            );
        }
    }

    public function positiveProvider(): array
    {
        return [
            [
                'image:150x200 ',
                self::id('image', 0, 5),
                self::imageSize(6, 7, '150', '200'),
            ],
            [
                'image:150x200:t ',
                self::id('image', 0, 5),
                self::imageSize(6, 9, '150', '200', true),
            ],
            [
                'image:2pxx200cm:f }',
                self::id('image', 0, 5),
                self::imageSize(6, 11, '2px', '200cm', false),
            ],
            [
                'image:size=100%x300px }',
                self::id('image', 0, 5),
                self::imageSize(6, 15, '100%', '300px'),
            ],
            [
                'image:size=999mm:333em }',
                self::id('image', 0, 5),
                self::imageSize(6, 16, '999mm', '333em'),
            ],
            [
                'image:width=1px:height=3:ratio=true ',
                self::id('image', 0, 5),
                self::imageSize(6, 29, '1px', '3', true),
            ],
            [
                'image:width=123:height=123:ratio=t}',
                self::id('image', 0, 5),
                self::imageSize(6, 28, '123', '123', true),
            ],
            [
                'image:width=123em:height=123px}',
                self::id('image', 0, 5),
                self::imageSize(6, 24, '123em', '123px'),
            ],
            [
                'image:width=123:ratio=false:height=132 ',
                self::id('image', 0, 5),
                self::imageSize(6, 32, '123', '132', false),
            ],
            [
                'image:width=123:ratio=true:height=132px}',
                self::id('image', 0, 5),
                self::imageSize(6, 33, '123', '132px', true),
            ],
            [
                'image:height=1px:width=2em:ratio=t ',
                self::id('image', 0, 5),
                self::imageSize(6, 28, '2em', '1px', true),
            ],
            [
                'image:height=2%:width=1%:ratio=false ',
                self::id('image', 0, 5),
                self::imageSize(6, 30, '1%', '2%', false),
            ],
            [
                'image:height=2%:width=1% ',
                self::id('image', 0, 5),
                self::imageSize(6, 18, '1%', '2%'),
            ],
            [
                'image:ratio=t:width=150:height=150 ',
                self::id('image', 0, 5),
                self::imageSize(6, 28, '150', '150', true),
            ],
            [
                'image:ratio=f:height=777pt:width=9999in }',
                self::id('image', 0, 5),
                self::imageSize(6, 33, '9999in', '777pt', false),
            ],
        ];
    }

    /**
     * @dataProvider negativeProvider
     *
     * @covers       \DocxTemplate\Lexer\Ast\Parser\ImageSizeParser::parse
     *
     * @param string $content
     * @param Identity $identity
     * @param string $exception
     * @throws InvalidSourceException
     * @throws SyntaxError
     */
    public function testParseNegative(string $content, Identity $identity, string $exception): void
    {
        $this->expectException($exception);
        foreach (self::reader($content) as $reader) {
            (new ImageSizeParser($reader, $identity))->parse();
        }
    }

    public function negativeProvider(): array
    {
        return [
            ['${ image:150ufx792 }', self::id('image', 3, 5), InvalidImageSizeException::class],
            ['${ image:bad-image-size }', self::id('image', 3, 5), InvalidImageSizeException::class],
            ['${ image:width=150px:width=560:height=29 }', self::id('image', 3, 5), InvalidImageSizeException::class],
            ['${ image:ratio=f:width=1px:height=2:ratio=true }', self::id('image', 3, 5), InvalidImageSizeException::class],
            ['${ image:150ufx792', self::id('image', 3, 5), EndNotFoundException::class],
        ];
    }
}