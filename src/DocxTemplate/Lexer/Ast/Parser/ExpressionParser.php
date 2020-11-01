<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\FilterExpression;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\SyntaxError;

class ExpressionParser extends Parser
{
    private AstNode $left;

    public function __construct(ReaderInterface $reader, AstNode $left)
    {
        parent::__construct($reader, $left->getPosition()->getEnd());
        $this->left = $left;
    }

    /** @inheritdoc  */
    public function parse(): ?AstNode
    {
        $pipe = $this->firstNotEmpty($this->getOffset());
        if ($pipe === null || $pipe->getFound() !== self::FILTER_PIPE) {
            return null;
        }

        $right = $this->identity($pipe->getEnd());
        if ($right === null) {
            throw new SyntaxError("Couldn't parse filter");
        }

        return new FilterExpression($this->left, $right);
    }
}
