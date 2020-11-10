<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

class EscapedChar extends Node
{
    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition()->toArray(),
        ];
    }
}