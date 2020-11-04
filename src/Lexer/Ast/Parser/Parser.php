<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\Condition;
use DocxTemplate\Lexer\Ast\Node\Expression;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\Image;
use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\Node\Str;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Contract\Ast\AstParser;
use DocxTemplate\Lexer\Contract\Reader;
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
    protected const SCOPE_START = '{';
    protected const SPACE = ' ';

    protected const SPECIAL_CHARS = [
        self::BLOCK_END,
        self::SCOPE_START,
        self::STR_BRACE,
        self::IMAGE_SIZE_DELIMITER,
        self::FILTER_PIPE,
        self::PARAMS_DELIMITER,
        self::PARAMS_OPEN,
        self::PARAMS_CLOSE,
        self::COND_THEN,
        self::COND_ELSE
    ];

    private Reader $reader;
    private int $offset;

    public function __construct(Reader $reader, int $offset)
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
            array_merge(Reader::EMPTY_CHARS, $needle),
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
     * Chain of expressions
     *
     * @param AstNode $left
     * @return Expression|null
     * @throws SyntaxError
     */
    final protected function expressionChain(AstNode $left): ?Expression
    {
        $expr = $this->expression($left);
        if ($expr === null) {
            return null;
        }

        while ($expr !== null) {
            $left = $expr;
            $expr = $this->expression($left);
        }

        return $left;
    }

    /**
     * Get some container
     * @param int $offset
     * @return AstNode|null
     */
    final protected function container(int $offset): ?AstNode
    {
        $next = $this->firstNotEmpty($offset);
        if ($next === null) {
            return null;
        }

        switch ($next->getFound()) {
            case self::BLOCK_START[0]:
                // ${...something
                return $this->block($next->getStart());
                break;
            case self::STR_BRACE;
                // `...something
                return $this->string($next->getStart());
                break;
            default:
                return null;
        }
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
    final protected function identity(int $offset): ?AstNode
    {
        return (new IdentityParser($this->reader, $offset))->parse();
    }

    /**
     * Parse Image node
     *
     * @param AstNode $id
     * @return AstNode|Image|null
     * @throws SyntaxError
     */
    final protected function image(AstNode $id): ?Image
    {
        $size = $this->imageSize($id);
        return $size === null ? null : new Image($id, $size);
    }

    /**
     * Parse ImageSize node
     *
     * @param AstNode $identity
     * @return AstNode|ImageSize|null
     * @throws SyntaxError
     */
    final protected function imageSize(AstNode $identity): ?ImageSize
    {
        return (new ImageSizeParser($this->reader, $identity))->parse();
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