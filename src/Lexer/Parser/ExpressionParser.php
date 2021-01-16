<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Ast\Node\FilterExpression;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Lexer\Reader;

class ExpressionParser extends Parser
{
    private Node $left;

    public function __construct(Reader $reader, Node $left)
    {
        parent::__construct($reader, $left->getPosition()->getEnd());
        $this->left = $left;
    }

    /** @inheritdoc  */
    public function parse(): ?Node
    {
        $pipe = $this->firstNotEmpty($this->getOffset());
        if ($pipe === null || $pipe->getFound() !== self::FILTER_PIPE) {
            return null;
        }

        return new FilterExpression($this->left, $this->identity($pipe->getEnd()));
    }
}
