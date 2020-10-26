<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\Token\NestedAwareInterface;
use DocxTemplate\Lexer\Contract\Token\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

abstract class AbstractToken implements NestedAwareInterface
{
    private string $name;
    private TokenPosition $position;
    private array $nested;

    public function __construct(string $name, TokenPosition $position, TokenInterface ...$nested)
    {
        $this->name = trim(strip_tags(preg_replace('/\s+/u', ' ', $name)));
        $this->position = $position;
        $this->nested = $nested;
    }

    /** @inheritdoc  */
    public function getNested(): array
    {
        return $this->nested;
    }

    /** @inheritdoc  */
    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritdoc  */
    public function getPosition(): TokenPosition
    {
        return $this->position;
    }
}