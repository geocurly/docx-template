<?php

declare(strict_types=1);

namespace DocxTemplate\Ast;

class NodePosition
{
    private int $start;
    private int $length;

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
}