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
        if ($start !== null) {
            $end = $size->getPosition()->getEnd();
        } else {
            $end = $identity->getPosition()->getEnd();
        }

        parent::__construct(new NodePosition($start, $end - $start));

        $this->identity = $identity;
        $this->size = $size;
    }
}
