<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\Token\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

final class Ternary extends AbstractToken implements TokenInterface
{
    public const THEN_CHAR = '?';
    public const ELSE_CHAR = ':';

    private TokenInterface $if;
    private TokenInterface $then;
    private TokenInterface $else;

    public function __construct(
        string $name,
        TokenPosition $position,
        TokenInterface $if,
        TokenInterface $then,
        TokenInterface $else
    ) {
        parent::__construct($name, $position);

        $this->if = $if;
        $this->then = $then;
        $this->else = $else;
    }

    /**
     * Get "if" condition
     * @return TokenInterface
     */
    public function getIf(): TokenInterface
    {
        return $this->if;
    }

    /**
     * Get "then" condition
     * @return TokenInterface
     */
    public function getThen(): TokenInterface
    {
        return $this->then;
    }

    /**
     * Get "else" condition
     * @return TokenInterface
     */
    public function getElse(): TokenInterface
    {
        return $this->else;
    }
}