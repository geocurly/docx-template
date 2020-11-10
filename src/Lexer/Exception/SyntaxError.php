<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Exception;


class SyntaxError extends LexerException
{
    public function __construct(string $message, string $preview = '')
    {
        if ($preview !== '') {
            $message = "$message : '...$preview...'";
        }

        parent::__construct($message);
    }
}

