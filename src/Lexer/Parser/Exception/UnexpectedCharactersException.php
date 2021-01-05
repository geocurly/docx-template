<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser\Exception;

use DocxTemplate\Exception\Lexer\SyntaxError;

class UnexpectedCharactersException extends SyntaxError
{
    protected $message = "Unexpected characters were found";
}
