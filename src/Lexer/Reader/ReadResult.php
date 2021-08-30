<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

/**
 * @codeCoverageIgnore
 */
class ReadResult
{
    public function __construct(
        private string $found,
        private int $start,
        private int $length,
    ) {
    }

    public function getFound(): string
    {
        return $this->found;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        if ($this->getLength() === 0) {
            return $this->getStart();
        }

        return $this->getStart() + $this->getLength();
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
