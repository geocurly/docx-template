<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Contract;

use Psr\Http\Message\StreamInterface;

interface SourceInterface
{
    /**
     * Get character streams
     * @return iterable|StreamInterface[] = [
     *      "$docxInnerFilename1" => "$psr7ContentStream1",
     *      "$docxInnerFilename2" => "$psr7ContentStream2",
     * ]
     */
    public function getStreams(): iterable;
}
