<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract;

interface ReaderInterface
{
    /**
     * Find sequence of chars and return found position
     *
     * @param string $needle
     * @param int $startPosition
     * @return array|null = [
     *      $start,
     *      $length
     * ]
     */
    public function find(string $needle, int $startPosition = 0): ?array;

    /**
     * Find any of sequences and return found position
     *
     * @param array $needles
     * @param int $startPosition
     * @return array|null
     */
    public function findAny(array $needles, int $startPosition = 0): ?array;

    /**
     * Get sequence if it first after $startPosition
     *
     * @param array $needles any character sequence
     * @param int $startPosition
     * @return array|null
     */
    public function firstNotEmpty(array $needles, int $startPosition = 0): ?array;
    
    /**
     * Find and read content between given strings
     * @param string $fromString start read from this string
     * @param string $toString read to this string
     * @param int $startPosition position in the source
     * @return array = [
     *      $content,
     *      $startOfContent,
     *      $lengthOfContent,
     * ]
     */
    public function betweenSequences(string $fromString, string $toString, int $startPosition = 0): ?array;

    /**
     * Read bytes from content
     *
     * @param int $startPosition start position
     * @param int $bytes bytes to read
     * @return string|null
     */
    public function read(int $startPosition, int $bytes): ?string;
}
