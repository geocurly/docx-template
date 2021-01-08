<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser\Exception;

use DocxTemplate\Exception\Lexer\SyntaxErrorException;

class EndNotFoundException extends SyntaxErrorException
{
    protected $message = "Couldn't find the end of element";
}
