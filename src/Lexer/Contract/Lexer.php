<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract;

use DocxTemplate\Lexer\Exception\SyntaxError;

interface Lexer
{
    /**
     * Start parsing content and iterate blocks
     * @return iterable
     * @throws SyntaxError
     */
    public function run(): iterable;
}
