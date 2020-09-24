<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class StreamReaderTest extends TestCase
{
    /**
     * @covers \DocxTemplate\Lexer\StreamReader::find
     */
    public function testFind(): void
    {
        $content = 'There is some text and <tags>Hello!</tags> $<some-tag-between-open>{macro}';

        $reader = $this->reader($content);
        $this->assertSame($reader->find('${macro}'), [43, 31]);

        $reader = $this->reader($content);
        $this->assertSame($reader->find('${'), [43, 25]);

        $reader = $this->reader($content);
        $this->assertSame($reader->find('$'), [43, 1]);

        $reader = $this->reader($content);
        $this->assertSame($reader->find('$', 20), [43, 1]);

        $reader = $this->reader($content);
        $this->assertSame($reader->find('${'), [43, 25]);
    }

    /**
     * @covers \DocxTemplate\Lexer\StreamReader::findAndReadBetween
     */
    public function testReadBetween(): void
    {
        $content = 'There is some text and <tags>Hello!</tags> $<some-tag-between-open>{macro}}';
        $reader = $this->reader($content);
        $this->assertSame(
            ['$<some-tag-between-open>{macro}}', 43, 32],
            $reader->findAndReadBetween('${', '}}'),
        );

        $reader = $this->reader($content);
        $this->assertSame(
            ['$<some-tag-between-open>{macro}', 43, 31],
            $reader->findAndReadBetween('$', '}'),
        );

        $reader = $this->reader($content);
        $this->assertSame(
            ['$<some-tag-between-open>{macro}', 43, 31],
            $reader->findAndReadBetween('$', '}', 20),
        );

        $reader = $this->reader($content);
        $this->assertSame(
            ['$<some-tag-between-open>{macro}}', 43, 32],
            $reader->findAndReadBetween('${', '}}', 20),
        );
    }

    private function reader(string $content): StreamReader
    {
        return new StreamReader(stream_for($content));
    }
}