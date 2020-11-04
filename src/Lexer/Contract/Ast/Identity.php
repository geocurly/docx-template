<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract\Ast;

interface Identity extends AstNode
{
    /**
     * Get node identity
     * @return string
     */
    public function getId(): string;
}
