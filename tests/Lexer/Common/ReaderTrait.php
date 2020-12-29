<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Common;

use DocxTemplate\Contract\Lexer\Reader;
use DocxTemplate\Exception\Lexer\InvalidSourceException;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use GuzzleHttp\Psr7\Utils;

trait ReaderTrait
{
    /**
     * @param string $content
     * @return Reader[]
     * @throws InvalidSourceException
     */
    protected static function reader(string $content): iterable
    {
        yield new StreamReader(Utils::streamFor($content));
        yield new StringReader($content);
    }
}
