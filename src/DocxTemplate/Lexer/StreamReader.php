<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use Psr\Http\Message\StreamInterface;

class StreamReader
{
    private int $length;
    private int $position;
    private string $content;

    public function __construct(StreamInterface $stream)
    {
        $this->content = $stream->getContents();
        $this->length = strlen($this->content);
        $this->position = 0;
    }

    /**
     * Find string and stop at that position
     * @param string $needle
     * @param int $position
     * @return array|null = [
     *      $start,
     *      $end
     * ]
     */
    public function find(string $needle, int $position = 0): ?array
    {
        $length = strlen($needle);
        if ($length > 1) {
            $found = $this->findFrom($needle, $position);
            if ($found === null) {
                return null;
            }

            return [$found[0], $found[1]];
        }

        $offset = $position === 0 ? 0 : $position + 1;
        $found = strpos($this->content, $needle, $offset);

        if ($found === false) {
            return null;
        }

        return [$found, 1];
    }

    /**
     * Find adn read content between given strings
     * @param string $from
     * @param string $to
     * @param int $position
     * @return array = [
     *      $content,
     *      $startOfNeedle,
     *      $endOfNeedle,
     * ]
     */
    public function findAndReadBetween(string $from, string $to, int $position = 0): ?array
    {
        [$start, $end] = $this->find($from, $position) ?? [null, null];
        if ($end === null) {
            return null;
        }

        return $this->findAndRead($to, $start);
    }

    /**
     * Move and read content until $needle
     * @param string $needle
     * @param int $position
     * @return array|null = [
     *      $content,
     *      $startOfNeedle,
     *      $endOfNeedle,
     * ]
     */
    public function findAndRead(string $needle, int $position): ?array
    {
        return $this->readUntil($needle, $position);
    }

    /**
     * Go to beginning
     * @return $this
     */
    public function rewind(): self
    {
        $this->position = 0;
        return $this;
    }

    /**
     * Check if end of content
     * @return bool
     */
    public function eof(): bool
    {
        return $this->position >= $this->length;
    }

    /**
     * Find next char position
     * @param string $char
     * @param int $position
     * @return int|null = $position
     */
    private function findChar(string $char, int $position): ?int
    {
        $pos = strpos($this->content, $char, $position);
        if ($pos === false) {
            return null;
        }

        return $pos;
    }

    /**
     * Find needle in content
     * @param string $needle
     * @param int $position
     * @return array|null = [
     *      $from,
     *      $length
     * ]
     */
    private function findFrom(string $needle, int $position): ?array
    {
        $positions = [];
        $offset = $position;
        foreach (str_split($needle) as $num => $char) {
            $positions[$num] = $this->findChar($char, $offset);
            if ($positions[$num] === null) {
                return null;
            }

            $offset = $positions[$num] + 1;
        }

        // Some word processor may add any tags between chars. Check it
        $realLength = $positions[$num] - $positions[0] + 1;
        $content = substr($this->content, $positions[0], $realLength);

        if ($positions === [] || $content === false || strip_tags($content) !== $needle) {
            return null;
        }

        return [$positions[0], $realLength];
    }

    /**
     * Read content from $startPosition to $needle string
     *
     * @param string $needle
     * @param int $position
     * @return array|null
     */
    private function readUntil(string $needle, int $position): ?array
    {
        $length = strlen($needle);
        if ($length > 1) {
            [$from, $length] = $this->findFrom($needle, $position) ?? [null, null];
            if ($length === null) {
                return null;
            }

            // Get first found position
            $length = $from + $length - $position;
            return [
                substr(
                    $this->content,
                    $position,
                    $length
                ),
                $position,
                $length
            ];
        }

        $charPos = $this->findChar($needle, $position);
        if ($charPos === false) {
            return null;
        }

        $length = $charPos - $position + 1;
        return [substr($this->content, $position, $length), $position, $length];
    }
}
