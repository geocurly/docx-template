<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Inclusive;
use DocxTemplate\Contract\Ast\Node as NodeInterface;

/**
 * @codeCoverageIgnore
 */
final class Str extends Node implements Inclusive
{
    private array $nested;
    private string $content;

    public function __construct(NodePosition $position, string $content, Inclusive ...$nested)
    {
        parent::__construct($position);
        $this->nested = $nested;
        $this->content = $content;
    }

    /**
     * Get nested nodes
     * @return Inclusive[]
     */
    public function getNested(): array
    {
        return $this->nested;
    }

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'position' => $this->getPosition()->toArray(),
            'nested' => array_map(fn(NodeInterface $node) => $node->toArray(), $this->nested)
        ];
    }

    /** @inheritdoc  */
    public function getContent(): string
    {
        return $this->content;
    }
}