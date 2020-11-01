<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract;

interface ReaderInterface
{
    public const EMPTY_CHARS = [" ", "\t", "\n", "\r", "\0", "\x0B"];

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
     * @param int $position
     * @return array|null
     */
    public function firstNotEmpty(int $position): ?array;

    /**
     * Read bytes from content
     *
     * @param int $startPosition start position
     * @param int $bytes bytes to read
     * @return string|null
     */
    public function read(int $startPosition, int $bytes): ?string;
}
