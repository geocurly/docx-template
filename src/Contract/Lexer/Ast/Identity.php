<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Lexer\Ast;

interface Identity extends AstNode
{
    /**
     * Get node identity
     * @return string
     */
    public function getId(): string;
}
