<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\SourceInterface;

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
                $position = $reader->findAndMove('${');
                if ($position === null) {
                    continue 2;
                }

                [$macro, $start, $end] = $reader->findAndRead('}') ?? [null];
                if ($macro === null) {
                    continue 2;
                }

                [$macro, $start, $end] = ["\${{$macro}}", $start - 2, $end + 1];
                if (isset($this->tokens[$macro]['files'])) {
                    $this->tokens[$macro]['files'][$file][] = [$start, $end - $start];
                } else {
                    $this->tokens[$macro] = [
                        'type' => self::TYPE_SIMPLE_VARIABLE,
                        'files' => [$file => [[$start, $end - $start]]],
                    ];
                }
            }
        }

        return $this->tokens;
    }
}
