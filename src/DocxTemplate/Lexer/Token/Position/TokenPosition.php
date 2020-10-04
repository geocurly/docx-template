<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token\Position;

class TokenPosition
{
    private string $source;
    private int $start;
    private int $length;

    public function __construct(string $source, int $start, int $length)
    {
        $this->source = $source;
        $this->start = $start;
        $this->length = $length;
    }

    /**
     * Get source name in zip
     * @return string
     */
    public function getSourceName(): string
    {
        return $this->source;
    }

    /**
     * Get start of token
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * Get end of token
     * @return int
     */
    public function getEnd(): int
    {
        if ($this->getLength() === 0) {
            return $this->getStart();
        }

        return $this->getStart() + $this->getLength();
    }

    /**
     * Get token length
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }
}
