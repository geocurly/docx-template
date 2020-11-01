<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract\Ast;

use DocxTemplate\Lexer\Ast\NodePosition;

interface AstNode
{
    /**
     * Get node position at given content
     * @return NodePosition
     */
    public function getPosition(): NodePosition;
}
