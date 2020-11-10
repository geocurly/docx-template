<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\FilterExpression;
use DocxTemplate\Lexer\Ast\Parser\Exception\ElementNotFoundException;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Contract\Reader;
use DocxTemplate\Lexer\Exception\SyntaxError;

class ExpressionParser extends Parser
{
    private AstNode $left;

    public function __construct(Reader $reader, AstNode $left)
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
            throw new ElementNotFoundException(
                'Filter identity not found',
                $this->read($this->getOffset(), $pipe->getEnd() + 20)
            );
        }

        return new FilterExpression($this->left, $right);
    }
}
