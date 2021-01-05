<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node as NodeInterface;
use DocxTemplate\Contract\Ast\Inclusive;

class Block extends Node implements Inclusive
{
    private array $nested;
    private string $content;

    public function __construct(NodePosition $position, string $content, NodeInterface ...$nested)
    {
        parent::__construct($position);
        $this->nested = $nested;
        $this->content = $content;
    }

    /**
     * Get nested nodes
     * @return NodeInterface[]
     */
    public function getNested(): array
    {
        return $this->nested;
    }

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition()->toArray(),
            'nested' => array_map(fn(NodeInterface $node) => $node->toArray(), $this->nested)
        ];
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}