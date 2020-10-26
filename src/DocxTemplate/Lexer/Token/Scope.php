<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\Token\NestedAwareInterface;

final class Scope extends AbstractToken implements NestedAwareInterface
{
    public const OPEN = '${';
    public const CLOSE = '}';
}