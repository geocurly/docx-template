<?php

declare(strict_types=1);

namespace DocxTemplate\Ast;

class NodePosition
{
    private int $start;
    private int $length;
    private ?NodePosition $next = null;

    public function __construct(int $start, int $length)
    {
        $this->start = $start;
        $this->length = $length;
    }

    /**
     * Get start of node
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * Get end of node
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
     * Get node length
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Transform to array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'start' => $this->getStart(),
            'end' => $this->getEnd(),
        ];
    }

    /**
     * Add next position
     * @param NodePosition $next
     * @return $this
     *
     * @codeCoverageIgnore
     */
    public function addNext(NodePosition $next): self
    {
        $this->next = $next;
        return $this;
    }

    /**
     * Change length to given
     * @param int $start
     * @param int|null $length
     * @return $this
     */
    public function change(int $start, int $length = null): self
    {
        $diff = $start - $this->getStart();
        if ($length !== null) {
            $diff +=  $length - $this->getLength();
            $this->length = $length;
        }

        $this->start = $start;
        if ($this->next !== null) {
            $this->next->change($this->next->getStart() + $diff);
        }

        return $this;
    }
}