<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Reader;

use DocxTemplate\Exception\Lexer\InvalidSourceException;
use Psr\Http\Message\StreamInterface;

class StreamReader extends AbstractReader
{
    private const CHUNK_SIZE = 1024;

    private StreamInterface $stream;

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
    protected function findAnyChar(array $chars, int $position): ?array
    {
        $offset = $position;
        $chars = array_merge($chars, ['<']);

        try {
            $this->stream->seek($offset);
        } catch (\RuntimeException $exception) {
            return null;
        }

        $content = $this->stream->read(self::CHUNK_SIZE);
        $subst = strpbrk($content, implode('', $chars));
        // Try to find in next chunk
        if ($subst === false) {
            $offset += self::CHUNK_SIZE;
            $found = $this->findAnyChar($chars, $offset);
            if ($found === null) {
                return null;
            }

            [$subst, $offset] = [$found[0], $found[1]];
        } else {
            $offset = $offset + strpos($content, $subst, 0);
        }

        // Skip all tags
        if ($subst[0] === '<') {
            $close = $this->findAnyChar(['>'], $offset + 1);
            if ($close === null) {
                // There is no end of tag
                return null;
            }

            // Continue common search
            $found = $this->findAnyChar($chars, $close[1] + 1);
            if ($found === null) {
                return null;
            }

            [$subst, $offset] = $found;
        }

        return [$subst[0], $offset];
    }

    /** @inheritDoc */
    public function readRaw(int $start, int $bytes): ?string
    {
        try {
            $this->stream->seek($start);
        } catch (\RuntimeException $exception) {
            return null;
        }

        $read = $this->stream->read($bytes);
        return $read === '' ? null : $read;
    }
}
