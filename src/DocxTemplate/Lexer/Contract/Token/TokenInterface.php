<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract\Token;

use DocxTemplate\Lexer\Token\Position\TokenPosition;

interface TokenInterface
{
    /**
     * Get token name
     * @return string
     */
    public function getName(): string;

    /**
     * Get token position
     * @return TokenPosition
     */
    public function getPosition(): TokenPosition;
}
