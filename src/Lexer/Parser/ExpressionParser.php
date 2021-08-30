<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Ast\Node\FilterExpression;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Lexer\Reader;

final class ExpressionParser extends Parser
{
    public function __construct(Reader $reader, private Node $left)
    {
        parent::__construct($reader, $left->getPosition()->getEnd());
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
