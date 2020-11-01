<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

class Block extends Node
{
    private array $nested;

    public function __construct(NodePosition $position, AstNode ...$nested)
    {
        parent::__construct($position);
        $this->nested = $nested;
    }
}