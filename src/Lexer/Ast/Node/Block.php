<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

class Block extends Node
{
    private array $nested;
    private bool $isEscaped;

    public function __construct(
        NodePosition $position,
        bool $isEscaped,
        AstNode ...$nested
    ) {
        parent::__construct($position);
        $this->nested = $nested;
        $this->isEscaped = $isEscaped;
    }

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition()->toArray(),
            'isEscaped' => $this->isEscaped,
            'nested' => array_map(fn(AstNode $node) => $node->toArray(), $this->nested)
        ];
    }
}