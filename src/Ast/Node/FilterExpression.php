<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Contract\Ast\Identity;

class FilterExpression extends Expression implements Identity
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

    /** @inheritdoc  */
    public function getId(): string
    {
        return $this->getRight()->getId();
    }
}
