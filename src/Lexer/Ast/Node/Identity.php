<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\Identity as IdentityInterface;

class Identity extends Node implements IdentityInterface
{
    private string $id;

    public function __construct(string $id, NodePosition $position)
    {
        parent::__construct($position);
        $this->id = trim(preg_replace('/\s+/', ' ', $id));
    }

    /**
     * Get identity of node
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
