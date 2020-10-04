<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Scope implements TokenInterface
{
    public const OPEN = '${';
    public const CLOSE = '}';

    private TokenPosition $position;
    private array $nested;
    private string $name;

    public function __construct(string $name, TokenPosition $position, TokenInterface ...$nested)
    {
        $this->position = $position;
        $this->nested = $nested;
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