<?php

declare(strict_types=1);

namespace DocxTemplate\Ast;

use ArrayIterator;
use DocxTemplate\Lexer\Parser\BlockParser;
use DocxTemplate\Contract\Lexer\Reader;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use IteratorAggregate;
use Traversable;

class Ast implements IteratorAggregate
{
    private Reader $reader;

    private array $blocks = [];

    /**
     * Ast constructor.
     * @param Reader $reader
     * @throws SyntaxErrorException
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->build();
    }

    /**
     * Build Abstract Syntax Tree
     *
     * @return $this
     * @throws SyntaxErrorException
     */
    private function build(): self
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