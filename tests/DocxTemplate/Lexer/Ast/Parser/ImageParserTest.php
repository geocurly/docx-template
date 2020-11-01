<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\Image;
use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class ImageParserTest extends TestCase
{
    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\ImageParser::image
     * @dataProvider imageProvider
     *
     * @param string $content
     * @param int $pos
     * @param AstNode|null $expected
     * @throws InvalidSourceException|SyntaxError
     */
    public function testImagePositive(string $content, int $pos, ?AstNode $expected): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                $expected,
                (new ImageParser($reader, $pos))->parse(),
                "Try to find image in '$content'. " . get_class($reader)
            );
        }
    }

    public function imageProvider(): array
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
            ]
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
