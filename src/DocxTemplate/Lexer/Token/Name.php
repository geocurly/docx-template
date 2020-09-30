<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Name implements TokenInterface
{
    private TokenPosition $position;
    private string $name;

    public function __construct(string $name, TokenPosition $position)
    {
        $this->position = $position;
        $this->name = $name;
    }

    /** @inheritDoc */
    public function getPosition(): TokenPosition
    {
        return $this->position;
    }

    /** @inheritDoc */
    public function getName(): string
    {
        return $this->name;
    }
}
