<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Lexer;

use DocxTemplate\Exception\Lexer\SyntaxError;

interface Lexer
{
    /**
     * Start parsing content and iterate blocks
     * @return iterable
     * @throws SyntaxError
     */
    public function run(): iterable;
}
