<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Reader;

use DocxTemplate\Exception\Lexer\InvalidSourceException;
use DocxTemplate\Lexer\Reader\ReadResult;
use DocxTemplate\Tests\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \DocxTemplate\Lexer\Reader\StreamReader
 * @covers  \DocxTemplate\Lexer\Reader\StringReader
 */
class ReaderTest extends TestCase
{
    use ReaderTrait;

    /**
     * @dataProvider findAnySequenceDataProvider
     *
     * @param string $content
     * @param array $args
     * @param array|null $expect
     * @throws InvalidSourceException
     */
    public function testFindAnySequence(string $content, array $args, ?array $expect): void
    {
        $given = '"' . implode(',', $args[0]) . '"';
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                $expect === null ? null : new ReadResult(...$expect),
                $reader->findAny(...$args),
                "Try to first of given $given with " . get_class($reader)
            );
        }
    }

    public function findAnySequenceDataProvider(): array
    {
        $content = 'There is `<tags>$<tags>{some<tags>}';
        return [
            [$content, [['${', '}']], ['${', 16, 8]],
            [$content, [['$', '`$']], ['`$', 9, 8]],
            [$content, [['?', '}']], ['}', 34, 1]],
            [$content, [['${', '}'], 20], ['}', 34, 1]],
            [$content, [['$', '`$'], 20], null],
            [$content, [['?', '}'], 20], ['}', 34, 1]],
            ['image<w:t>:size</w:t>', [[':'], 0], [':', 10, 1]],
            ['find \`escaped \${', [['\`', '\${'], 0], ['\`', 5, 2]],
            ['find \`escaped \${', [['\${', '\`'], 0], ['\`', 5, 2]]
        ];
    }

    /**
     * @dataProvider firstNotEmptyDataProvider
     *
     * @param string $content
     * @param int $pos
     * @param array|null $expect
     * @throws InvalidSourceException
     */
    public function testFirstNotEmpty(string $content, int $pos, ?array $expect): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                new ReadResult(...$expect),
                $reader->firstNotEmpty($pos),
                "Try to get next not empty char in '$content' from $pos with " . get_class($reader)
            );
        }
    }

    public function firstNotEmptyDataProvider(): array
    {
        return [
            [' `macro` }', 0, ['`', 1, 1],],
            ["   \n    macro` }", 3, ['m', 8, 1]],
            [" $  \n  \t  \${}` }", 3, ['$', 10, 1]],
            [
                "<simple-variable> one_<bold>two</bold> <style>| simple-name \n</style>}</simple-variable>",
                0,
                ['o', 18, 1]
            ]
        ];
    }

    /**
     *
     * @dataProvider readProvider
     *
     * @param string $content
     * @param int $pos
     * @param int $bytes
     * @param string $expect
     * @throws InvalidSourceException
     */
    public function testRead(string $content, int $pos, int $bytes, string $expect): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertSame(
                $expect,
                $reader->read($pos, $bytes),
                "Try to read $bytes bytes from $pos position in $content. " . get_class($reader)
            );
        }
    }

    public function readProvider(): array
    {
        return [
            ['There is some string', 6, 7, 'is some'],
            ['There is <bold>some</bold> string', 6, 7, 'is '],
            ['There is <bold>some</bold> string', 6, 9, 'is '],
            ['There is <bold>some <br>string</bold> with tags', 11, 31, 'old>some string with'],
        ];
    }
}