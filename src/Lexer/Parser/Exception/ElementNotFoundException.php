<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser\Exception;

use DocxTemplate\Exception\Lexer\SyntaxError;

/** @codeCoverageIgnore  */
class ElementNotFoundException extends SyntaxError
{
    protected $message = 'Element not found';
}
