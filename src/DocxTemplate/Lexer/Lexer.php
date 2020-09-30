<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\SourceInterface;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use DocxTemplate\Lexer\Token\Scope;

class Lexer
{
    public const TYPE_SIMPLE_VARIABLE = 0;
    private SourceInterface $source;

    private array $tokens = [];

    public function __construct(SourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * Parse Docx document to find all defined tokens
     * @return array
     */
    public function parse(): array
    {
        foreach ($this->source->getStreams() as $file => $stream) {
            $reader = new StreamReader($stream);
            $position = 0;
            while (true) {
                $scope = Scope::parse($reader, $position);
            }
        }

        return $this->tokens;
    }

    /**
     * Find nested macro in parent content
     * @param string $parent
     * @return array|null
     */
    private function macro(string $parent): ?array
    {
        $reader = new StringReader($parent);
        $macro = $reader->betweenSequences('${', '}' ,0);
        dd($macro);
        if ($macro === null) {
            return null;
        }

        [$content, $start, $length] = $macro;
        // Find any nested macro
        $nested = $this->nested($content);
        if ($nested === null) {
            return [$content, $start, $length];
        }

        return [$content, $start, $length];
    }

    private function string(string $parent): ?array
    {
        return null;
    }

    private function nested(string $parent): ?array
    {
        $reader = new StringReader($parent);
        $found = $reader->find('$', 1);
        if ($found !== null) {
            // There is start of new nested macro
            return $this->macro($parent);
        }

        $found = $reader->find('`', 1);
        if ($found !== null) {
            // There is start of new string variable
            return $this->string($parent);
        }

        return null;
    }
}
