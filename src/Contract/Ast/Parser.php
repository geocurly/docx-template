<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Ast;

use DocxTemplate\Exception\Lexer\SyntaxErrorException;

interface Parser
{
    /**
     * Parse Node of AST
     *
     * @return Node|null
     *
     * @throws SyntaxErrorException
     */
    public function parse(): ?Node;
}
