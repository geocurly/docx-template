<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract\Token;

interface CallableInterface extends TokenInterface
{
    /**
     * Get callable token arguments
     * @return TokenInterface[]
     */
    public function getArgs(): array;
}
