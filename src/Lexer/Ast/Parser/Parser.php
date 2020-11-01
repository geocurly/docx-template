<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Condition;
use DocxTemplate\Lexer\Ast\Node\Expression;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\Image;
use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\Node\Str;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Contract\Ast\AstParser;
use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Lexer\Reader\ReadResult;

abstract class Parser implements AstParser
{
    protected const BLOCK_START = '${';
    protected const BLOCK_END = '}';
    protected const STR_BRACE = '`';
    protected const IMAGE_SIZE_DELIMITER = ':';
    protected const FILTER_PIPE = '|';
    protected const PARAMS_OPEN = '(';
    protected const PARAMS_CLOSE = ')';
    protected const PARAMS_DELIMITER = ',';
    protected const COND_THEN = '?';
    protected const COND_ELSE = ':';

    private ReaderInterface $reader;
    private int $offset;

    public function __construct(ReaderInterface $reader, int $offset)
    {
        $this->reader = $reader;
        $this->offset = $offset;
    }

    final protected function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Read content from given interval
     *
     * @param int $from
     * @param int $bytes
     * @return string
     */
    final protected function read(int $from, int $bytes): string
    {
        return (string) $this->reader->read($from, $bytes);
    }

    /**
     * Find first needle or empty char
     *
     * @param array $needle
     * @param int $offset
     * @return ReadResult|null
     */
    final protected function findAnyOrEmpty(array $needle, int $offset): ?ReadResult
    {
        return $this->findAny(
            array_merge(ReaderInterface::EMPTY_CHARS, $needle),
            $offset
        );
    }

    /**
     * Find first of $needle from $offset
     *
     * @param array $needle
     * @param int $offset
     * @return ReadResult|null
     */
    final protected function findAny(array $needle, int $offset): ?ReadResult
    {
        $found = $this->reader->findAny($needle, $offset);
        if ($found === null) {
            return null;
        }

        return new ReadResult(...$found);
    }

    /**
     * Find first not empty char
     *
     * @param int $offset
     * @return ReadResult|null
     */
    final protected function firstNotEmpty(int $offset): ?ReadResult
    {
        $found = $this->reader->firstNotEmpty($offset);
        return $found === null ? null : new ReadResult(...$found);
    }

    /**
     * Parse Identity node
     *
     * @param int $offset
     * @return AstNode|Identity|null
     * @throws SyntaxError
     */
    final protected function identity(int $offset): ?Identity
    {
        return (new IdentityParser($this->reader, $offset))->parse();
    }

    /**
     * Parse Image node
     *
     * @param int $offset
     * @return AstNode|Image|null
     * @throws SyntaxError
     */
    final protected function image(int $offset): ?Image
    {
        return (new ImageParser($this->reader, $offset))->parse();
    }

    /**
     * Parse ImageSize node
     *
     * @param int $offset
     * @return AstNode|ImageSize|null
     * @throws SyntaxError
     */
    final protected function imageSize(int $offset): ?ImageSize
    {
        return (new ImageSizeParser($this->reader, $offset))->parse();
    }

    /**
     * Parse Str node
     *
     * @param int $offset
     * @return AstNode|Str|null
     * @throws SyntaxError
     */
    final protected function string(int $offset): ?Str
    {
        return (new StrParser($this->reader, $offset))->parse();
    }

    /**
     * Parse Block node
     *
     * @param int $offset
     * @return Block|AstNode|null
     * @throws SyntaxError
     */
    final protected function block(int $offset): ?Block
    {
        return (new BlockParser($this->reader, $offset))->parse();
    }

    /**
     * Parse nested node
     *
     * @param int $offset
     * @return AstNode
     * @throws SyntaxError
     */
    final protected function nested(int $offset): ?AstNode
    {
        return (new NestedParser($this->reader, $offset))->parse();
    }

    /**
     * Parse Condition node
     *
     * @param AstNode $if
     * @return AstNode|Condition|null
     * @throws SyntaxError
     */
    final protected function condition(AstNode $if): ?Condition
    {
        return (new ConditionParser($this->reader, $if))->parse();
    }

    /**
     * Parse Expression node
     *
     * @param AstNode $left
     * @return AstNode|Expression|null
     * @throws SyntaxError
     */
    final protected function expression(AstNode $left): ?Expression
    {
        return (new ExpressionParser($this->reader, $left))->parse();
    }
}