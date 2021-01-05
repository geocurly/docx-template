<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Ast\Node\Block;
use DocxTemplate\Ast\Node\Condition;
use DocxTemplate\Ast\Node\Expression;
use DocxTemplate\Ast\Node\Identity;
use DocxTemplate\Ast\Node\Image;
use DocxTemplate\Ast\Node\ImageSize;
use DocxTemplate\Ast\Node\Str;
use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Ast\Parser as ParserInterface;
use DocxTemplate\Contract\Ast\Identity as IdentityInterface;
use DocxTemplate\Contract\Lexer\Reader;
use DocxTemplate\Exception\Lexer\SyntaxError;
use DocxTemplate\Lexer\Reader\ReadResult;

/** @codeCoverageIgnore  */
abstract class Parser implements ParserInterface
{
    protected const BLOCK_START = '${';
    protected const BLOCK_START_ESCAPED = '\${';
    protected const BLOCK_END = '}';
    protected const STR_BRACE = '`';
    protected const STR_BRACE_ESCAPED = '\`';
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
     * Get content
     * @param NodePosition $position
     * @return string
     */
    final protected function readBy(NodePosition $position): string
    {
        return (string) $this->read($position->getStart(), $position->getLength());
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
        return $this->reader->findAny($needle, $offset);
    }

    /**
     * Chain of expressions
     *
     * @param Node $left
     * @return Expression|null
     * @throws SyntaxError
     */
    final protected function expressionChain(Node $left): ?Expression
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
     * @return Node|null
     */
    final protected function container(int $offset): ?Node
    {
        $next = $this->firstNotEmpty($offset);
        if ($next === null) {
            return null;
        }

        switch ($next->getFound()) {
            case self::BLOCK_START[0]:
                // ${...something
                return $this->block($next->getStart());
            case self::STR_BRACE;
                // `...something
                return $this->string($next->getStart());
            case self::BLOCK_START_ESCAPED[0];
                $afterEscaped = $this->read($next->getEnd(), 1);
                if ($afterEscaped === self::BLOCK_START[0]) {
                    return $this->block($next->getStart());
                }

                if ($afterEscaped === self::STR_BRACE[0]) {
                    return $this->string($next->getStart());
                }

                return null;
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
        return $this->reader->firstNotEmpty($offset);
    }

    /**
     * Parse Identity node
     *
     * @param int $offset
     * @return Node|Identity|null
     * @throws SyntaxError
     */
    final protected function identity(int $offset): ?Node
    {
        return (new IdentityParser($this->reader, $offset))->parse();
    }

    /**
     * Parse Image node
     *
     * @param IdentityInterface $id
     * @return Node|Image|null
     * @throws SyntaxError
     */
    final protected function image(IdentityInterface $id): ?Image
    {
        $size = $this->imageSize($id);
        return $size === null ? null : new Image($id, $size);
    }

    /**
     * Parse ImageSize node
     *
     * @param IdentityInterface $identity
     * @return Node|ImageSize|null
     * @throws SyntaxError
     */
    final protected function imageSize(IdentityInterface $identity): ?ImageSize
    {
        return (new ImageSizeParser($this->reader, $identity))->parse();
    }

    /**
     * Parse Str node
     *
     * @param int $offset
     * @return Node|Str|null
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
     * @return Block|Node|null
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
     * @return Node
     * @throws SyntaxError
     */
    final protected function nested(int $offset): ?Node
    {
        return (new NestedParser($this->reader, $offset))->parse();
    }

    /**
     * Parse Condition node
     *
     * @param Node $if
     * @return Node|Condition|null
     * @throws SyntaxError
     */
    final protected function condition(Node $if): ?Condition
    {
        return (new ConditionParser($this->reader, $if))->parse();
    }

    /**
     * Parse Expression node
     *
     * @param Node $left
     * @return Node|Expression|null
     * @throws SyntaxError
     */
    private function expression(Node $left): ?Expression
    {
        return (new ExpressionParser($this->reader, $left))->parse();
    }
}
