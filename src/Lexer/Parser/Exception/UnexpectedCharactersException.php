<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser\Exception;

use DocxTemplate\Exception\Lexer\SyntaxErrorException;

class UnexpectedCharactersException extends SyntaxErrorException
{
    protected $message = "Unexpected characters were found";
}
