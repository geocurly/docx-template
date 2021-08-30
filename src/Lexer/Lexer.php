<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Ast\Ast;
use DocxTemplate\Contract\Lexer\Lexer as LexerInterface;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use Psr\Http\Message\StreamInterface;
use DocxTemplate\Lexer\{
    Reader\StreamReader,
    Reader\StringReader,
};

class Lexer implements LexerInterface
{
    /** @inheritDoc */
    public function run(string|StreamInterface $source): iterable
    {
        if ($source instanceof StreamInterface) {
            $reader = new StreamReader($source);
        } else {
            $reader = new StringReader($source);
        }

        return new Ast($reader);
    }
}
