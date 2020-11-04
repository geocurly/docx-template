<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

class Condition extends Node
{
    private AstNode $if;
    private AstNode $then;
    private AstNode $else;

    public function __construct(AstNode $if, AstNode $then, AstNode $else)
    {
        $position = new NodePosition(
            $if->getPosition()->getStart(),
            $else->getPosition()->getEnd() - $if->getPosition()->getStart()
        );

        parent::__construct($position);

        $this->if = $if;
        $this->then = $then;
        $this->else = $else;
    }
}