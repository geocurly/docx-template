<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Contract\Processor\Source\RelationContainer;

final class ProcessFactory
{
    /**
     * @param RelationContainer $container
     * @return iterable<SimpleContentProcess>
     */
    public function make(RelationContainer $container): iterable
    {
        $content = $container->getContent();
        $tables = $this->tables($content, 0);
        if ($tables === []) {
            yield new SimpleContentProcess($content, $container);
            return;
        }

        $offset = 0;
        foreach ($tables as [$tblPosition, $nested]) {
            $difference = $tblPosition[0] - $offset;
            if ($difference > 0) {
                yield new SimpleContentProcess(
                    substr($content, $offset, $tblPosition[0] - $offset),
                    $container
                );
            }

            yield new TableContentProcess(
                substr($content, $tblPosition[0], $tblPosition[1] - $tblPosition[0]),
                $container
            );

            $offset = $tblPosition[1];
        }

        if (strlen($content) > $offset) {
            yield new SimpleContentProcess(substr($content, $offset), $container);
        }
    }

    /**
     * Parse table in given content
     * @param string $base
     * @param int $offset
     * @return array[]|null
     */
    private function table(string $base, int $offset): ?array
    {
        $starts[] = $this->findStart($base, 'w:tbl', $offset);
        if ($starts[0] === null) {
            return null;
        }

        $end = $this->findEnd($base, 'w:tbl', $starts[0]);
        if ($end === null) {
            throw new \RuntimeException("Invalid table xml");
        }

        // There is may be nested table
        $nestedStart = $starts[0];
        while (true) {
            // Try to find it
            //              ↓
            // <w:tbl>...<w:tbl>...</w:tbl>...
            $nestedStart = $this->findStart($base, 'w:tbl', $nestedStart + 1);
            if ($nestedStart === null || $nestedStart >= $end) {
                break;
            }

            $starts[] = $nestedStart;
        }

        $nextStart = $this->findStart($base, 'w:tbl', $end);

        $depth = count($starts) - 1;
        // There is end of the deeper table
        $ends[$depth] = $end;
        $nestedEnd = $end;
        for ($index = $depth - 1; $index >= 0; --$index) {
            $nestedEnd = $this->findEnd($base, 'w:tbl', $nestedEnd + 1);
            if ($nestedEnd === null || ($nextStart !== null && $nestedEnd > $nextStart)) {
                throw new \RuntimeException("Unclosed table tags");
            }

            $ends[$index] = $nestedEnd;
        }

        $nested = [];
        for ($index = 1; $index <= $depth; $index++) {
            $nested[] = [$starts[$index], $ends[$index]];
        }

        return [[$starts[0], $ends[0]], $nested];
    }

    /**
     * Parse tables in given content
     * @param string $content
     * @param int $offset
     * @return array
     */
    private function tables(string $content, int $offset): array
    {
        $tables = [];
        $nextPosition = $offset;
        while (true) {
            $table = $this->table($content, $nextPosition);
            if ($table === null) {
                return $tables;
            }

            $nextPosition = $table[0][1];
            $tables[] = $table;
        }
    }

    /**
     * Find the start position of the nearest table row after $offset.
     *
     * @param string $base
     * @param string $needle
     * @param int $offset
     * @return int|null
     */
    private function findStart(string $base, string $needle, int $offset): ?int
    {
        $start = strpos($base, "<$needle ", $offset);

        if ($start === false) {
            $start = strpos($base, "<$needle>", $offset);
        }

        return $start === false ? null : $start;
    }

    /**
     * Find the end position of the nearest table row after $offset.
     * @param string $base
     * @param string $needle
     * @param int $offset
     * @return int
     */
    private function findEnd(string $base, string $needle, int $offset): int
    {
        return strpos($base, "</$needle>", $offset) + strlen($needle) + 3;
    }
}
