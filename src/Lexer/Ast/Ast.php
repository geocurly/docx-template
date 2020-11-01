<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast;

use ArrayIterator;
use DocxTemplate\Lexer\Ast\Parser\BlockParser;
use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\SyntaxError;
use IteratorAggregate;
use Traversable;

class Ast implements IteratorAggregate
{
    private ReaderInterface $reader;

    private array $blocks = [];

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Build Abstract Syntax Tree
     *
     * @return $this
     * @throws SyntaxError
     */
    public function build(): self
    {
        $position = 0;
        while (true) {
            $parser = new BlockParser($this->reader, $position);
            $block = $parser->parse();
            if ($block === null) {
                break;
            }

            $position = $block->getPosition()->getEnd();
            $this->blocks[] = $block;
        }

        return $this;
    }

    /** @inheritdoc  */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->blocks);
    }
}