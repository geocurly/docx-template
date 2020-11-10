<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

use DocxTemplate\Lexer\Contract\Reader;

abstract class AbstractReader implements Reader
{
    /** @inheritDoc */
    public function findAny(array $needles, int $offset = 0): ?ReadResult
    {
        $chars = [];
        foreach ($needles as $needle) {
            $chars[$needle[0]][] = $needle;
        }

        $first = $this->findAnyChar(array_keys($chars), $offset);
        if ($first === null) {
            return null;
        }

        $sequence = null;
        foreach ($chars[$first[0]] as $needle) {
            $sequence = $this->find($needle, $first[1]);
            if ($sequence !== null) {
                break;
            }
        }

        if ($sequence === null) {
            return null;
        }

        return new ReadResult($needle, ...$sequence);
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
    public function firstNotEmpty(int $startOffset = 0): ?ReadResult
    {
        $first = $this->readRaw($startOffset, 1);
        if ($first === null) {
            return null;
        }

        if (in_array($first, self::EMPTY_CHARS, true)) {
            $found = $this->firstNotEmpty($startOffset + 1);
        } elseif ($first === '<') {
            $position = $startOffset + 1;
            while (true) {
                $found = $this->firstNotEmpty($position);
                if ($found === null || $found->getFound() === '>') {
                    break;
                }

                $position = $found->getEnd();
            }

            $found = $this->firstNotEmpty($found->getEnd());
        } else {
            $found = new ReadResult($first, $startOffset, 1);
        }

        return $found;
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

        if ($positions === [] || $this->read($positions[0], $realLength) !== $multipleNeedle) {
            return null;
        }

        return [$positions[0], $realLength];
    }

    /** @inheritdoc  */
    public function read(int $startPosition, int $bytes): ?string
    {
        $read = $this->readRaw($startPosition, $bytes);
        return $read === null ? null : strip_tags($read);
    }

    /**
     * Find any char in stream from given position
     *
     * @param array $chars
     * @param int $position
     * @return array|null
     */
    abstract protected function findAnyChar(array $chars, int $position): ?array;

    /**
     * Read raw content
     *
     * @param int $start
     * @param $bytes
     * @return string|null
     */
    abstract protected function readRaw(int $start, int $bytes): ?string;
 }