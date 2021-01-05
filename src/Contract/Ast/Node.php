<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Ast;

use DocxTemplate\Ast\NodePosition;

interface Node
{
    /**
     * Get NodePosition
     * @return NodePosition
     */
    public function getPosition(): NodePosition;

    /**
     * Get type of node
     * @return string
     */
    public function getType(): string;

    /**
     * Transform to array
     * @return array
     */
    public function toArray(): array;
}
