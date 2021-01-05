<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Ast;

use DocxTemplate\Exception\Lexer\SyntaxError;

interface Parser
{
    /**
     * Parse Node of AST
     *
     * @return Node|null
     *
     * @throws SyntaxError
     */
    public function parse(): ?Node;
}
