<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Lexer;

use DocxTemplate\Lexer\Reader\ReadResult;

interface Reader
{
    public const EMPTY_CHARS = [" ", "\t", "\n", "\r", "\0", "\x0B"];

    /**
     * Find any of sequences and return found position
     *
     * @param array $needles
     * @param int $offset
     * @return array|null
     */
    public function findAny(array $needles, int $offset = 0): ?ReadResult;

    /**
     * Get sequence if it first after $startPosition
     *
     * @param int $offset
     * @return array|null
     */
    public function firstNotEmpty(int $offset): ?ReadResult;

    /**
     * Read bytes from content
     *
     * @param int $startPosition start position
     * @param int $bytes bytes to read
     * @return string|null
     */
    public function read(int $startPosition, int $bytes): ?string;
}
