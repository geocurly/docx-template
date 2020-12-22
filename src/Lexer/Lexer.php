<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use Psr\Http\Message\StreamInterface;
use DocxTemplate\Lexer\{
    Ast\Ast,
    Reader\StreamReader,
    Reader\StringReader,
    Contract\Lexer as LexerInterface,
    Contract\Reader
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
            throw new \InvalidArgumentException("Invalid source type: " . gettype($source));
        }
    }

    /** @inheritDoc */
    public function run(): iterable
    {
        $ast = new Ast($this->reader);
        return $ast->build();
    }
}
