<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class ReaderTest extends TestCase
{
    /**
     * @dataProvider findDataProvider
     * @covers       \DocxTemplate\Lexer\Reader\AbstractReader::find
     *
     * @param string $content
     * @param array $args
     * @param array $expected
     * @param string $message
     */
    public function testFindSequence(string $content, array $args, array $expected, string $message): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertSame($expected, $reader->find(...$args), "Try to $message.");
        }
    }

    public function findDataProvider(): array
    {
        $content1 = 'There is some text and <tags>Hello!</tags> $<some-tag-between-open>{macro}';
        return [
            // 1) Content to read
            // 2) Array of arguments
            // 3) Expected function return
            // 4) Description
            [$content1, ['${macro}'], [43, 31], 'find concrete word between braces from beginning'],
            [$content1, ['${'], [43, 25], 'find open macro characters from beginning'],
            [$content1, ['$'], [43, 1], 'find open macro char from beginning'],
            [$content1, ['$', 20], [43, 1], 'find open macro char from concrete position'],
            [$content1, ['${', 20], [43, 25], 'find open macro characters from concrete position'],
        ];
    }


    /**
     * @dataProvider readBetweenDataProvider
     * @covers       \DocxTemplate\Lexer\Reader\AbstractReader::betweenSequences
     *
     * @param string $content
     * @param array $args
     * @param array $expect
     * @param string $message
     */
    public function testReadBetween(string $content, array $args, array $expect, string $message): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertSame($expect, $reader->betweenSequences(...$args), "Try to $message.");
        }
    }

    public function readBetweenDataProvider(): array
    {
        $content1 = 'There is some text and <tags>Hello!</tags> $<some-tag-between-open>{macro}}';
        return [
            [
                $content1, // Content to read
                ['${', '}}', 0], // Array of arguments
                ['$<some-tag-between-open>{macro}}', 43, 32], // Expected function return
                "find content between \${ and }} from beginning", // Description
            ],
            [
                $content1, ['${', '}}', 20],
                ['$<some-tag-between-open>{macro}}', 43, 32],
                "find content between \${ and }} from concrete position"
            ],
            [
                $content1, ['$', '}', 0],
                ['$<some-tag-between-open>{macro}', 43, 31],
                "find content between \$ and } from beginning",
            ],
            [
                $content1,
                ['$', '}', 20],
                ['$<some-tag-between-open>{macro}', 43, 31],
                "find content between \$ and } from concrete position"
            ],
        ];
    }

    /**
     * @dataProvider findAnySequenceDataProvider
     * @covers \DocxTemplate\Lexer\Reader\AbstractReader::findAny
     *
     * @param string $content
     * @param array $args
     * @param array|null $expect
     * @param string $message
     */
    public function testFindAnySequence(string $content, array $args, ?array $expect, string $message): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertSame(
                $expect,
                $reader->findAny(...$args),
                "Try to $message with " . get_class($reader)
            );
        }
    }

    public function findAnySequenceDataProvider(): array
    {
        $content = 'There is `<tags>$<tags>{some<tags>}';
        return [
            [$content, [['${', '}']], ['${', 16, 8], 'find first of given strings: "${", "}"'],
            [$content, [['$', '`$']], ['`$', 9, 8], 'find first of given string: "$", "`$"'],
            [$content, [['?', '}']], ['}', 34, 1], 'find first of given string: "?", "}"'],
            [$content, [['${', '}'], 20], ['}', 34, 1], 'find first of given strings: "${", "}" from 20 bytes'],
            [$content, [['$', '`$'], 20], null, 'find first of given string: "$", "`$" from 20 bytes'],
            [$content, [['?', '}'], 20], ['}', 34, 1], 'find first of given string: "?", "}" from 20 bytes'],
        ];
    }

    /**
     * @covers \DocxTemplate\Lexer\Reader\AbstractReader::firstNotEmpty
     * @dataProvider firstNotEmptyDataProvider
     *
     * @param string $content
     * @param array $args
     * @param array|null $expect
     * @param string $message
     */
    public function testFirstNotEmpty(string $content, array $args, ?array $expect, string $message): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertSame(
                $expect,
                $reader->firstNotEmpty(...$args),
                "Try to $message with " . get_class($reader)
            );
        }
    }

    public function firstNotEmptyDataProvider(): array
    {
        return [
            [
                '${ `macro` }',
                [['`', ':']],
                null,
                'find next strings "`", ":"'
            ],
            [
                '  ${ `macro`}',
                [['${', '`']],
                ['${', 2, 2],
                'find next strings "${", "`"'
            ],
            [
                " \n \r \$macro1 ?: \$macro2",
                [['$', ':', '?']],
                ['$', 5, 1],
                'find next strings "$", ":", "?"'
            ],
            [
                "\t `\${nested} variable` \n \r \$macro1 ?: \$macro2",
                [['$', ':', '?', '`']],
                ['`', 2, 1],
                'find next strings "$", ":", "?", "`"',
            ],
            [
                "\t     \n? `\$then` : `\$else`",
                [['?']],
                ['?', 7, 1],
                'find next string "?"',
            ]
        ];
    }

    /**
     * @param string $content
     * @return iterable|ReaderInterface[]
     * @throws \DocxTemplate\Lexer\Exception\InvalidSourceException
     */
    private function reader(string $content): iterable
    {
        yield new StreamReader(stream_for($content));
        yield new StringReader($content);
    }
}