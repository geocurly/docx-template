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
     * Transform to array
     * @return array
     */
    public function toArray(): array;
}
