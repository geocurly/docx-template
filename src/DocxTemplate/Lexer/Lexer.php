<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\SourceInterface;
use function GuzzleHttp\Psr7\stream_for;

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
            while (!$reader->eof()) {
                $macro = $reader->findAndReadBetween('${', '}');
                if ($macro === null) {
                    continue 2;
                }

                [$content, $start, $length] = $macro;
                $nested = $this->nested($content);
                if ($nested !== null) {
                    continue 2;
                }

                $content = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
                if (isset($this->tokens[$content]['files'])) {
                    $this->tokens[$content]['files'][$file][] = [$start, $length];
                } else {
                    $this->tokens[$content] = [
                        'type' => self::TYPE_SIMPLE_VARIABLE,
                        'files' => [$file => [[$start, $length]]],
                    ];
                }
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
        $reader = new StreamReader(stream_for($parent));
        $macro = $reader->findAndReadBetween('${', '}' ,0);
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
        $beginning = trim($parent[0] === '<' ? strip_tags($parent)[0] : $parent[0]);
        if ($beginning === '$') {
            // There is start of new nested macro
            return $this->macro($parent);
        }

        if ($beginning === '`') {
            // There is start of new string variable
            return $this->string($parent);
        }

        return null;
    }
}
