<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Inclusive;

/**
 * @codeCoverageIgnore
 */
final class EscapedChar extends Node implements Inclusive
{
    private string $content;

    public function __construct(NodePosition $position, string $content)
    {
        parent::__construct($position);
        $this->content = $content;
    }

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition()->toArray(),
        ];
    }

    /** @inheritdoc  */
    public function getContent(): string
    {
        return $this->content;
    }
}