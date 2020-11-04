<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

class Image extends Node
{
    private AstNode $identity;
    private ?ImageSize $size;

    public function __construct(AstNode $identity, ?ImageSize $size)
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
