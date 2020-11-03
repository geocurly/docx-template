<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser\Exception;

use DocxTemplate\Lexer\Exception\SyntaxError;

class UnexpectedCharactersException extends SyntaxError
{
    protected $message = "Unexpected characters were found";
}
