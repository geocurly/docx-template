<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract\Token;

interface NestedAwareInterface extends TokenInterface
{
    /**
     * Get nested tokens
     * @return TokenInterface[]
     */
    public function getNested(): array;
}