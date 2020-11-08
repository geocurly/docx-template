<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

abstract class Node implements AstNode
{
    private NodePosition $position;

    public function __construct(NodePosition $position)
    {
        $this->position = $position;
    }

    public function getPosition(): NodePosition
    {
        return $this->position;
    }

    protected function getType(): string
    {
        return substr(static::class, strrpos(static::class, '\\') + 1);
    }
}