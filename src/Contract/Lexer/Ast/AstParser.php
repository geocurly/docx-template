<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Lexer\Ast;

use DocxTemplate\Exception\Lexer\SyntaxError;

interface AstParser
{
    /**
     * Parse Node of AST
     *
     * @return AstNode|null
     *
     * @throws SyntaxError
     */
    public function parse(): ?AstNode;
}
