<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node as NodeInterface;

/**
 * @codeCoverageIgnore
 */
abstract class Expression extends Node
{
    private NodeInterface $left;
    private NodeInterface $right;

    public function __construct(NodeInterface $left, NodeInterface $right)
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
     * @return Node
     */
    public function getLeft(): NodeInterface
    {
        return $this->left;
    }

    /**
     * Get right operand
     * @return Node
     */
    public function getRight(): NodeInterface
    {
        return $this->right;
    }
}
