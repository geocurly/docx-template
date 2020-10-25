<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use DocxTemplate\Lexer\Token\Scope;
use Psr\Http\Message\StreamInterface;

class Lexer
{
    private array $ast = [];
    private ReaderInterface $reader;

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
     * Parse content to find all defined tokens
     * @return array
     */
    public function parse(): array
    {
        $tokens = [];
        $parser = new TokenParser($this->reader);
        $position = 0;
        while (true) {
            $scope = $parser->scope($position);
            if ($scope === null) {
                break;
            }

            $position = $scope->getPosition()->getEnd();
            $tokens[] = $scope;
        }

        return $tokens;
    }
}
