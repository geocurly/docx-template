<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser\Exception;

use DocxTemplate\Lexer\Exception\SyntaxError;

class UnsupportedArgumentException extends SyntaxError
{
    protected $message = "Element couldn't have any arguments.";
}
