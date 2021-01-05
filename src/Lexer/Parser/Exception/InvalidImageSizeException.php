<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser\Exception;

use DocxTemplate\Exception\Lexer\SyntaxError;

/** @codeCoverageIgnore  */
class InvalidImageSizeException extends SyntaxError
{
    protected $message = 'Invalid image size.';
}
