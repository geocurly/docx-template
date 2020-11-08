<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\Identity as IdentityInterface;

class Image extends Node implements IdentityInterface
{
    private IdentityInterface $identity;
    private ?ImageSize $size;

    public function __construct(IdentityInterface $identity, ?ImageSize $size)
    {
        $start = $identity->getPosition()->getStart();
        if ($start !== null) {
            $end = $size->getPosition()->getEnd();
        } else {
            $end = $identity->getPosition()->getEnd();
        }

        parent::__construct(new NodePosition($start, $end - $start));

        $this->identity = $identity;
        $this->size = $size;
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
            'size' => $this->size->toArray(),
        ];
    }
}
