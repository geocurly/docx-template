<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

abstract class Expression extends Node
{
    private AstNode $left;
    private AstNode $right;

    public function __construct(AstNode $left, AstNode $right)
    {
        parent::__construct(
            new NodePosition(
                $left->getPosition()->getStart(),
                $right->getPosition()->getEnd() - $left->getPosition()->getStart()
            )
        );
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * Get left operand
     * @return AstNode
     */
    public function getLeft(): AstNode
    {
        return $this->left;
    }

    /**
     * Get right operand
     * @return AstNode
     */
    public function getRight(): AstNode
    {
        return $this->right;
    }
}
