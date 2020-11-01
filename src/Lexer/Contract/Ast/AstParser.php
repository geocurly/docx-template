<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract\Ast;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\SyntaxError;

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
