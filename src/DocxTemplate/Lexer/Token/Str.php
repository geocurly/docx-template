<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\Token\NestedAwareInterface;
use DocxTemplate\Lexer\Contract\Token\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Str implements NestedAwareInterface
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

    /** @inheritdoc  */
    public function getNested(): array
    {
        return $this->nested;
    }
}
