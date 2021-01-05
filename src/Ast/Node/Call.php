<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node as NodeInterface;
use DocxTemplate\Contract\Ast\Identity as IdentityInterface;

class Call extends Node implements IdentityInterface
{
    private IdentityInterface $identity;
    private array $params;

    public function __construct(IdentityInterface $identity, NodePosition $position, NodeInterface ...$params)
    {
        parent::__construct($position);
        $this->identity = $identity;
        $this->params = $params;
    }

    /** @inheritdoc  */
    public function getId(): string
    {
        return $this->identity->getId();
    }

    /**
     * Get all params
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition()->toArray(),
            'identity' => $this->identity->toArray(),
            'params' => array_map(fn(NodeInterface $node) => $node->toArray(), $this->params)
        ];
    }
}