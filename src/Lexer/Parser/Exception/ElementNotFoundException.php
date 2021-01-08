<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser\Exception;

use DocxTemplate\Exception\Lexer\SyntaxErrorException;

class ElementNotFoundException extends SyntaxErrorException
{
    protected $message = 'Element not found';
}
