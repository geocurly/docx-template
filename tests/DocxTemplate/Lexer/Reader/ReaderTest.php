<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class ReaderTest extends TestCase
{
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
                [0, ['`', ':']],
                null,
                'find next strings "`", ":"'
            ],
            [
                '  ${ `macro`}',
                [0, ['${', '`']],
                ['${', 2, 2],
                'find next strings "${", "`"'
            ],
            [
                " \n \r \$macro1 ?: \$macro2",
                [0, ['$', ':', '?']],
                ['$', 5, 1],
                'find next strings "$", ":", "?"'
            ],
            [
                "\t `\${nested} variable` \n \r \$macro1 ?: \$macro2",
                [0, ['$', ':', '?', '`']],
                ['`', 2, 1],
                'find next strings "$", ":", "?", "`"',
            ],
            [
                "\t     \n? `\$then` : `\$else`",
                [0, ['?']],
                ['?', 7, 1],
                'find next string "?"',
            ],
            [
                ' `macro` }',
                [0],
                ['`', 1, 1],
                "get next not empty char for ' `macro` }'"
            ],
            [
                "   \n    macro` }",
                [3],
                ['m', 8, 1],
                "get next not empty char for ' \"   \\n    macro` }\" }'"
            ],
            [
                " $  \n  \t  \${}` }",
                [3],
                ['$', 10, 1],
                "get next not empty char for ' $  \\n  \\t  \${}` }'"
            ],
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