<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;

class Image extends Node
{
    private Identity $identity;
    private ?ImageSize $size;

    public function __construct(Identity $identity, ?ImageSize $size)
    {
        $start = $identity->getPosition()->getStart();
        $length = $identity->getPosition()->getLength() + ($size === null ? 0 :$size->getPosition()->getLength());
        parent::__construct(new NodePosition($start, $length));

        $this->identity = $identity;
        $this->size = $size;
    }
}
