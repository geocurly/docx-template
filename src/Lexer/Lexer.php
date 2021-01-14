<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Ast\Ast;
use DocxTemplate\Contract\Lexer\Lexer as LexerInterface;
use DocxTemplate\Contract\Lexer\Reader;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use Psr\Http\Message\StreamInterface;
use DocxTemplate\Lexer\{
    Reader\StreamReader,
    Reader\StringReader,
};

class Lexer implements LexerInterface
{
    private Reader $reader;

    public function __construct($source)
    {
        if (is_string($source)) {
            $this->reader = new StringReader($source);
        } elseif ($source instanceof StreamInterface) {
            $this->reader = new StreamReader($source);
        } else {
            throw new InvalidSourceException("Invalid source type: " . gettype($source));
        }
    }

    /** @inheritDoc */
    public function run(): iterable
    {
        return new Ast($this->reader);
    }
}
