<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Ast\Ast;
use DocxTemplate\Lexer\Contract\Reader;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use Psr\Http\Message\StreamInterface;

class Lexer
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

    /**
     * Parse content to AbstractSyntaxTree
     *
     * @return Ast
     * @throws Exception\SyntaxError
     */
    public function parse(): Ast
    {
        $ast = new Ast($this->reader);
        return $ast->build();
    }
}
