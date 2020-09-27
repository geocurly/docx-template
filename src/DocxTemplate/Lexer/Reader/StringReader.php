<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

class StringReader extends AbstractReader
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }
    
    /** @inheritDoc */
    protected function findChar(string $char, int $startPosition): ?int
    {
        $position = strpos($this->content, $char, $startPosition);
        if ($position === false) {
            return null;
        }

        return $position;
    }

    /** @inheritDoc */
    protected function findAnyChar(array $chars, int $startPosition): ?array
    {
        $content = substr($this->content, $startPosition);
        $subst = strpbrk($content, implode($chars));
        if ($subst === false) {
            return null;
        }

        return [$subst[0], strpos($content, $subst)];
    }

    /** @inheritDoc */
    public function read(int $startPosition, int $bytes): ?string
    {
        return substr($this->content, $startPosition, $bytes);
    }
}