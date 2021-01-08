<?php

declare(strict_types=1);

namespace DocxTemplate\Exception\Lexer;

class SyntaxErrorException extends LexerException
{
    public function __construct(string $message, string $preview = '')
    {
        if ($preview !== '') {
            $message = "$message : '...$preview...'";
        }

        parent::__construct($message);
    }
}
