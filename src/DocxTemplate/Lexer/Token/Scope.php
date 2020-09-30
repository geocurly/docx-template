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
    /** @var TokenInterface[] $needles needle tokens */
    private array $needles;
    private string $name;

    public function __construct(string $name, TokenPosition $position, TokenInterface ...$needles)
    {
        $this->position = $position;
        $this->needles = $needles;
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