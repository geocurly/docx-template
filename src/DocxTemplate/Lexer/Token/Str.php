<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Str implements TokenInterface
{
    public const BRACE = "`";

    private TokenPosition $position;
    /** @var TokenInterface[] $needles needle tokens */
    private array $needles;

    public function __construct(TokenPosition $position, ...$needles)
    {
        $this->position = $position;
        $this->needles = $needles;
    }

    public function getPosition(): TokenPosition
    {
        return $this->position;
    }
}
