<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Str implements TokenInterface
{
    public const BRACE = "`";

    private TokenPosition $position;
    private array $nested;
    private string $name;

    public function __construct(string $name, TokenPosition $position, TokenInterface ...$nested)
    {
        $this->position = $position;
        $this->nested = $nested;
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
