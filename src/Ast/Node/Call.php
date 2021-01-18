<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node as NodeInterface;
use DocxTemplate\Contract\Ast\Identity as IdentityInterface;

/**
 * @codeCoverageIgnore
 */
final class Call extends Node implements IdentityInterface
{
    private IdentityInterface $identity;
    private array $args;

    public function __construct(IdentityInterface $identity, NodePosition $position, NodeInterface ...$args)
    {
        parent::__construct($position);
        $this->identity = $identity;
        $this->args = $args;
    }

    /** @inheritdoc  */
    public function getId(): string
    {
        return $this->identity->getId();
    }

    /** @inheritdoc */
    public function toArray(): array
    {
        return [
            'position' => $this->getPosition()->toArray(),
            'identity' => $this->identity->toArray(),
            'args' => array_map(fn(NodeInterface $node) => $node->toArray(), $this->args)
        ];
    }

    /** @inheritdoc */
    public function getType(): string
    {
        return self::VALUABLE;
    }

    /** @inheritdoc */
    public function getArgs(): array
    {
        return $this->args;
    }
}