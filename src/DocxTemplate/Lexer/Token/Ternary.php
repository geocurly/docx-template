<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Ternary implements TokenInterface
{
    public const THEN_CHAR = '?';
    public const ELSE_CHAR = ':';

    private TokenPosition $position;
    private TokenInterface $if;
    private TokenInterface $then;
    private TokenInterface $else;
    private string $name;

    public function __construct(
        string $name,
        TokenPosition $position,
        TokenInterface $if,
        TokenInterface $then,
        TokenInterface $else
    ) {
        $this->position = $position;
        $this->if = $if;
        $this->then = $then;
        $this->else = $else;
        $this->name = $name;
    }

    public function getPosition(): TokenPosition
    {
        return $this->position;
    }

    public function getName(): string
    {
        return $this->name;
    }
}