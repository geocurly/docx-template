<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use Psr\Http\Message\StreamInterface;

class StreamReader
{
    private int $length;
    private int $position;
    private string $content;

    public function __construct(StreamInterface $stream)
    {
        $this->content = $stream->getContents();
        $this->length = strlen($this->content);
        $this->position = 0;
    }

    /**
     * Find string and stop at that position
     * @param string $needle
     * @return int|null
     */
    public function findAndMove(string $needle): ?int
    {
        $offset = $this->position === 0 ? 0 : $this->position + 1;
        $position = strpos($this->content, $needle, $offset);

        if ($position === false) {
            return null;
        }

        $this->position = $position + strlen($needle);
        return $position;
    }

    /**
     * Move and read content until $needle
     * @param string $needle
     * @return array|null = [
     *      $content,
     *      $startOfNeedle,
     *      $endOfNeedle,
     * ]
     */
    public function findAndRead(string $needle): ?array
    {
        $startOfContent = $this->position;
        $content = strstr(
            substr($this->content, $startOfContent),
            $needle,
            true
        );

        if ($content === false) {
            return null;
        }

        $endOfContent = $startOfContent + strlen($content);
        $this->position = $endOfContent + strlen($needle);
        return [$content, $startOfContent, $endOfContent];
    }

    /**
     * Check if end of content
     * @return bool
     */
    public function eof(): bool
    {
        return $this->position >= $this->length;
    }
}
