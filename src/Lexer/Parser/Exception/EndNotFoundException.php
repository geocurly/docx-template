<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser\Exception;

use DocxTemplate\Exception\Lexer\SyntaxError;

class EndNotFoundException extends SyntaxError
{
    protected $message = "Couldn't find the end of element";
}
