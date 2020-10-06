<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Call extends Name
{
    public const ARGS_OPEN = '(';
    public const ARGS_CLOSE = ')';
    public const COMMA = ',';
    private array $args;

    public function __construct(string $name, TokenPosition $position, TokenInterface ...$args)
    {
        parent::__construct($name, $position);
        $this->args = $args;
    }
}
