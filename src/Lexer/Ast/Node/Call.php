<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Contract\Lexer\Ast\AstNode;
use DocxTemplate\Contract\Lexer\Ast\Identity as IdentityInterface;

class Call extends Node implements IdentityInterface
{
    private IdentityInterface $identity;
    private array $params;

    public function __construct(IdentityInterface $identity, NodePosition $position, AstNode ...$params)
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

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition()->toArray(),
            'identity' => $this->identity->toArray(),
            'params' => array_map(fn(AstNode $node) => $node->toArray(), $this->params)
        ];
    }
}