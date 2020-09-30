<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract;

use DocxTemplate\Lexer\Token\Position\TokenPosition;

interface TokenInterface
{
    public function getName(): string;

    public function getPosition(): TokenPosition;
}
