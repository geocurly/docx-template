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
    protected function findAnyChar(array $chars, int $position): ?array
    {
        $content = substr($this->content, $position);
        $subst = strpbrk($content, implode($chars));
        if ($subst === false) {
            return null;
        } else {
            $position = $position + strpos($content, $subst);
        }

        // Skip all tags
        if ($subst[0] === '<') {
            $close = strpos($content, '>', $position);
            if ($close === false) {
                // There is no end of tag
                return null;
            }

            // Continue common search
            $found = $this->findAnyChar($chars, $close + 1);
            if ($found === null) {
                return null;
            }

            [$subst, $position] = $found;
        }

        return [$subst[0], $position];
    }

    /** @inheritDoc */
    public function read(int $startPosition, int $bytes): ?string
    {
        $content = substr($this->content, $startPosition, $bytes);
        if ($content === false) {
            return null;
        }

        return $content;
    }
}