<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract\Ast;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\SyntaxError;

interface AstParser
{
    public const BLOCK_START = '${';
    public const BLOCK_END = '}';

    /**
     * Parse Node of AST
     *
     * @param ReaderInterface $reader content reader
     * @param int $offset read position from
     * @return AstNode|null
     *
     * @throws SyntaxError
     */
    public function parse(ReaderInterface $reader, int $offset): ?AstNode;
}
