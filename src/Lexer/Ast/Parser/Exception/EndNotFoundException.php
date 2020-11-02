<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser\Exception;

use DocxTemplate\Lexer\Exception\SyntaxError;

class EndNotFoundException extends SyntaxError
{
    protected $message = "Couldn't find the end of element";
}
