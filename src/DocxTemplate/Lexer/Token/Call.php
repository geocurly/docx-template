<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\Token\CallableInterface;

final class Call extends AbstractToken implements CallableInterface
{
    public const ARGS_OPEN = '(';
    public const ARGS_CLOSE = ')';
    public const COMMA = ',';

    /** @inheritDoc */
    public function getArgs(): array
    {
        return $this->getNested();
    }
}
