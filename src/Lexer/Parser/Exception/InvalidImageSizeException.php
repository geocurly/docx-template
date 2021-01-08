<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser\Exception;

use DocxTemplate\Exception\Lexer\SyntaxErrorException;

class InvalidImageSizeException extends SyntaxErrorException
{
    protected $message = 'Invalid image size.';
}
