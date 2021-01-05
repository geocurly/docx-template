<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node as NodeInterface;

abstract class Node implements NodeInterface
{
    private NodePosition $position;

    public function __construct(NodePosition $position)
    {
        $this->position = $position;
    }

    public function getPosition(): NodePosition
    {
        return $this->position;
    }

    public function getType(): string
    {
        return substr(static::class, strrpos(static::class, '\\') + 1);
    }
}