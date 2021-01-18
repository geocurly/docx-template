<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Contract\Ast\Identity;

/**
 * @codeCoverageIgnore
 */
final class FilterExpression extends Expression implements Identity
{
    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
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

    /** @inheritdoc  */
    public function getArgs(): array
    {
        return $this->getRight()->getArgs();
    }

    /** @inheritdoc  */
    public function getType(): string
    {
        return self::FILTER;
    }
}
