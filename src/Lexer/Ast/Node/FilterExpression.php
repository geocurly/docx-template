<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

class FilterExpression extends Expression
{
    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition()->toArray(),
            'left' => $this->getLeft()->toArray(),
            'right' => $this->getRight()->toArray(),
        ];
    }
}
