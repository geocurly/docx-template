<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use ArrayIterator;
use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;
use DocxTemplate\Lexer\Token\Scope;
use IteratorAggregate;
use Traversable;

class Ast implements IteratorAggregate
{
    private ReaderInterface $reader;
    private TokenPosition $previous;
    /** @var Scope[] */
    private array $scopes = [];

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
        $this->previous = new TokenPosition(0, 0);
    }

    /**
     * Build Abstract Syntax Tree
     *
     * @return $this
     * @throws Exception\SyntaxError
     */
    public function build(): self
    {
        $parser = new TokenParser($this->reader);
        while (true) {
            $scope = $parser->scope($this->previous->getEnd());
            if ($scope === null) {
                break;
            }

            $this->previous = $scope->getPosition();
            $this->scopes[] = $scope;
        }

        return $this;
    }

    /** @inheritdoc  */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->scopes);
    }
}