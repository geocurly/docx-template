<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

use DocxTemplate\Lexer\Contract\ReaderInterface;

abstract class AbstractReader implements ReaderInterface
{
    /** @inheritDoc */
    public function find(string $needle, int $position = 0): ?array
    {
        $length = strlen($needle);
        if ($length > 1) {
            $found = $this->findMultiple($needle, $position);
            if ($found === null) {
                return null;
            }

            return [$found[0], $found[1]];
        }

        $found = $this->findChar($needle, $position);

        if ($found === null) {
            return null;
        }

        return [$found, 1];
    }

    /** @inheritDoc */
    public function betweenSequences(string $from, string $to, int $position = 0): ?array
    {
        [$start, $length] = $this->find($from, $position) ?? [null, null];
        if ($length === null) {
            return null;
        }

        return $this->readBefore($to, $start);
    }

    /** @inheritDoc */
    public function findAny(array $needles, int $startPosition = 0): ?array
    {
        $chars = [];
        foreach ($needles as $needle) {
            $chars[$needle[0]] = $needle;
        }

        $first = $this->findAnyChar(array_keys($chars), $startPosition);
        if ($first === null) {
            return null;
        }

        $sequence = $this->find($chars[$first[0]], $first[1]);
        if ($sequence === null) {
            return null;
        }

        return [$chars[$first[0]], ...$sequence];
    }

    /** @inheritDoc */
    public function nextNotEmpty(int $startPosition = 0): ?array
    {
        $first = $this->read($startPosition, 1);
        if ($first === null) {
            return null;
        }

        if (in_array($first, self::EMPTY_CHARS, true)) {
            $found = $this->nextNotEmpty($startPosition + 1);
        } elseif ($first === '<') {
            $position = $startPosition + 1;
            while (true) {
                $found = $this->nextNotEmpty($position);
                if ($found === null || $found[0] === '>') {
                    break;
                }

                $position = $found[1] + 1;
            }

            $found = $this->nextNotEmpty($found[1] + 1);
        } else {
            $found = [$first, $startPosition, 1];
        }

        return $found;
    }

    /** @inheritDoc */
    public function firstNotEmpty(array $needles, int $startPosition = 0): ?array
    {
        $next = $this->nextNotEmpty($startPosition);
        if ($next === null) {
            return null;
        }

        $firstChars = array_map(fn(string $needle) => $needle[0], $needles);
        if (!in_array($next[0], $firstChars, true)) {
            return null;
        }

        $firstAny = $this->findAny($needles, $startPosition);
        if ($firstAny === null) {
            return null;
        }

        $anyContent = $this->read($startPosition, $firstAny[1] + $firstAny[2]) ?? '';
        if (!trim(strip_tags($anyContent)) === '') {
            return null;
        }

        return $firstAny;
    }

    /**
     * Find needle in content
     * @param string $multipleNeedle
     * @param int $position
     * @return array|null = [
     *      $from,
     *      $length
     * ]
     */
    private function findMultiple(string $multipleNeedle, int $position): ?array
    {
        $positions = [];
        $offset = $position;
        foreach (str_split($multipleNeedle) as $num => $char) {
            $positions[$num] = $this->findChar($char, $offset);
            if ($positions[$num] === null) {
                return null;
            }

            $offset = $positions[$num] + 1;
        }

        // Some word processor may add any tags between chars. Check it
        $realLength = $positions[$num] - $positions[0] + 1;
        $content = $this->read($positions[0], $realLength);

        if ($positions === [] || $content === false || strip_tags($content) !== $multipleNeedle) {
            return null;
        }

        return [$positions[0], $realLength];
    }


    /**
     * Read content from $position to $needle string
     *
     * @param string $needle
     * @param int $position
     * @return array|null = [
     *      $content,
     *      $startOfNeedle,
     *      $length,
     * ]
     */
    private function readBefore(string $needle, int $position): ?array
    {
        $length = strlen($needle);
        if ($length > 1) {
            [$from, $length] = $this->findMultiple($needle, $position) ?? [null, null];
            if ($length === null) {
                return null;
            }

            // Get first found position
            $length = $from + $length - $position;
            return [
                $this->read($position, $length),
                $position,
                $length
            ];
        }

        $charPos = $this->findChar($needle, $position);
        if ($charPos === null) {
            return null;
        }

        $length = $charPos - $position + 1;
        return [$this->read($position, $length), $position, $length];
    }


    /**
     * Find char in stream from given position
     *
     * @param int $startPosition
     * @param string $char
     * @return int|null
     */
    abstract protected function findChar(string $char, int $startPosition): ?int;

    /**
     * Find any char in stream from given position
     *
     * @param array $chars
     * @param int $startPosition
     * @return array|null
     */
    abstract protected function findAnyChar(array $chars, int $startPosition): ?array;
 }