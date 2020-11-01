<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Common;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Reader\StreamReader;
use DocxTemplate\Lexer\Reader\StringReader;
use GuzzleHttp\Psr7\Utils;

trait ReaderTrait
{
    /**
     * @param string $content
     * @return ReaderInterface[]
     * @throws InvalidSourceException
     */
    protected static function reader(string $content): iterable
    {
        yield new StreamReader(Utils::streamFor($content));
        yield new StringReader($content);
    }

    /**
     * Make a node position mock
     *
     * @param int $start
     * @param int $length
     * @return NodePosition
     */
    protected static function pos(int $start, int $length): NodePosition
    {
        return new NodePosition($start, $length);
    }
}
