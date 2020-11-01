<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

class Call extends Node
{
    private Identity $identity;
    private array $params;

    public function __construct(Identity $identity, NodePosition $position, AstNode ...$params)
    {
        parent::__construct($position);
        $this->identity = $identity;
        $this->params = $params;
    }
}