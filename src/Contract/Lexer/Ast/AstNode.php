<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Lexer\Ast;

use DocxTemplate\Lexer\Ast\NodePosition;

interface AstNode
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
