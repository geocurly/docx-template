<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser\Exception;

use DocxTemplate\Lexer\Exception\SyntaxError;

class InvalidImageSizeException extends SyntaxError
{
    protected $message = 'Invalid image size.';
}