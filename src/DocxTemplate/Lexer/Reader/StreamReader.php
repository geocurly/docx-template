<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

use DocxTemplate\Lexer\Exception\InvalidSourceException;
use Psr\Http\Message\StreamInterface;

class StreamReader extends AbstractReader
{
    private StreamInterface $stream;
    private int $chunkSize = 1024;

    /**
     * StreamReader constructor.
     * @param StreamInterface $stream
     * @throws InvalidSourceException
     */
    public function __construct(StreamInterface $stream)
    {
        if (!$stream->isSeekable()) {
            throw new InvalidSourceException("Source stream is not seekable");
        }

        $this->stream = $stream;
    }

    /** @inheritDoc */
    protected function findChar(string $char, int $from): ?int
    {
        $offset = $from;
        foreach ($this->readChunk($offset) as $content) {
            $position = strpos($content, $char, 0);
            if ($position === false) {
                $offset += $this->chunkSize;
                continue;
            }

            return $offset + $position;
        }

        return null;
    }

    /**
     * Read portions from stream
     * @param int $position
     * @return iterable
     */
    private function readChunk(int $position): iterable
    {
        try {
            $this->stream->seek($position);
        } catch (\RuntimeException $exception) {
            return;
        }

        while(!$this->stream->eof()) {
            yield $this->stream->read($this->chunkSize);
        }
    }

    /** @inheritDoc */
    protected function findAnyChar(array $chars, int $startPosition): ?array
    {
        $offset = $startPosition;
        foreach ($this->readChunk($offset) as $content) {
            $subst = strpbrk($content, implode($chars));
            if ($subst === false) {
                $offset += $this->chunkSize;
                continue;
            }

            return [$subst[0], $offset + strpos($content, $subst, 0)];
        }

        return null;
    }

    /** @inheritDoc */
    public function read(int $from, int $bytes): ?string
    {
        try {
            $this->stream->seek($from);
        } catch (\RuntimeException $exception) {
            return null;
        }

        return $this->stream->read($bytes);
    }
}