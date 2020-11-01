<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class ReaderTest extends TestCase
{
    /**
     * @dataProvider findAnySequenceDataProvider
     * @covers       \DocxTemplate\Lexer\Reader\AbstractReader::findAny
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
            $this->assertSame(
                $expect,
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
        ];
    }

    /**
     * @covers       \DocxTemplate\Lexer\Reader\AbstractReader::firstNotEmpty
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
            $this->assertSame(
                $expect,
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
        ];
    }


    /**
     * @param string $content
     * @return iterable|ReaderInterface[]
     * @throws InvalidSourceException
     */
    private function reader(string $content): iterable
    {
        yield new StreamReader(stream_for($content));
        yield new StringReader($content);
    }
}