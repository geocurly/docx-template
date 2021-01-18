<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node as NodeInterface;

/**
 * @codeCoverageIgnore
 */
final class Condition extends Node
{
    private NodeInterface $if;
    private NodeInterface $then;
    private NodeInterface $else;

    public function __construct(NodeInterface $if, NodeInterface $then, NodeInterface $else)
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

    public function getIf(): NodeInterface
    {
        return $this->if;
    }

    public function getThen(): NodeInterface
    {
        return $this->then;
    }

    public function getElse(): NodeInterface
    {
        return $this->else;
    }

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'position' => $this->getPosition()->toArray(),
            'if' => $this->if->toArray(),
            'then' => $this->then->toArray(),
            'else' => $this->else->toArray(),
        ];
    }
}
