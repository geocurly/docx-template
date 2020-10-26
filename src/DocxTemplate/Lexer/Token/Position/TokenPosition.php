<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token\Position;

class TokenPosition
{
    private int $start;
    private int $length;
    private ?TokenPosition $next = null;

    public function __construct(int $start, int $length)
    {
        $this->start = $start;
        $this->length = $length;
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

    /**
     * Bind with next position
     * @param TokenPosition $next
     * @return $this
     */
    public function bind(TokenPosition $next): self
    {
        $this->next = $next;
        return $this;
    }

    /**
     * Get next position
     * @return TokenPosition|null
     */
    public function next(): ?TokenPosition
    {
        return $this->next;
    }
}
