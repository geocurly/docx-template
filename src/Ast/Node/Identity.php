<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Identity as IdentityInterface;

/**
 * @codeCoverageIgnore
 */
final class Identity extends Node implements IdentityInterface
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

    /** @inheritdoc */
    public function toArray(): array
    {
        return [
            'position' => $this->getPosition()->toArray(),
            'id' => $this->getId(),
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
        return [];
    }
}
