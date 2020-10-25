<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

use DocxTemplate\Lexer\Contract\ReaderInterface;

abstract class AbstractReader implements ReaderInterface
{
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

    /**
     * Find sequence of chars and return found position
     *
     * @param string $needle
     * @param int $position
     * @return array|null = [
     *      $start,
     *      $length
     * ]
     */
    private function find(string $needle, int $position = 0): ?array
    {
        $length = strlen($needle);
        if ($length > 1) {
            $found = $this->findMultiple($needle, $position);
            if ($found === null) {
                return null;
            }

            return [$found[0], $found[1]];
        }

        $found = $this->findAnyChar([$needle], $position);

        if ($found === null) {
            return null;
        }

        return [$found[1], 1];
    }

    /** @inheritDoc */
    private function getNextNotEmpty(int $startPosition = 0): ?array
    {
        $first = $this->read($startPosition, 1);
        if ($first === null) {
            return null;
        }

        if (in_array($first, self::EMPTY_CHARS, true)) {
            $found = $this->getNextNotEmpty($startPosition + 1);
        } elseif ($first === '<') {
            $position = $startPosition + 1;
            while (true) {
                $found = $this->getNextNotEmpty($position);
                if ($found === null || $found[0] === '>') {
                    break;
                }

                $position = $found[1] + 1;
            }

            $found = $this->getNextNotEmpty($found[1] + 1);
        } else {
            $found = [$first, $startPosition, 1];
        }

        return $found;
    }

    /** @inheritDoc */
    public function firstNotEmpty(int $position, ?array $needles = null): ?array
    {
        $next = $this->getNextNotEmpty($position);
        if ($next === null) {
            return null;
        }

        if ($needles === null || $needles === []) {
            return $next;
        }

        $firstChars = array_map(fn(string $needle) => $needle[0], $needles);
        if (!in_array($next[0], $firstChars, true)) {
            return null;
        }

        $firstAny = $this->findAny($needles, $position);
        if ($firstAny === null) {
            return null;
        }

        $anyContent = $this->read($position, $firstAny[1] + $firstAny[2]) ?? '';
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
            $found = $this->findAnyChar([$char], $offset);
            if ($found === null) {
                return null;
            }
            $positions[$num] = $found[1];
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
     * Find any char in stream from given position
     *
     * @param array $chars
     * @param int $position
     * @return array|null
     */
    abstract protected function findAnyChar(array $chars, int $position): ?array;
 }