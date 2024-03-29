<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Ast\Node\Condition;
use DocxTemplate\Lexer\Parser\Exception\InvalidImageSizeException;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Lexer\Reader;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;

class ConditionParser extends Parser
{
    public function __construct(Reader $reader, private Node $if)
    {
        parent::__construct($reader, $if->getPosition()->getEnd());
    }

    /** @inheritdoc  */
    public function parse(): ?Node
    {
        // $if ? ...
        $if = $this->if;
        $thenChar = $this->firstNotEmpty($this->getOffset());
        if ($thenChar === null || $thenChar->getFound() !== self::COND_THEN) {
            return null;
        }

        $elseChar = $this->firstNotEmpty($thenChar->getEnd());
        if ($elseChar !== null && $elseChar->getFound() === self::COND_ELSE) {
            // There is ?:
            $then = $if;
        } else {
            // ${ $if ? `string` ...} or ${ $if ? ${block} ... } or ${ $if ? name ... }
            $then = $this->then($thenChar->getEnd());
            if ($then === null) {
                throw new SyntaxErrorException(
                    "Couldn't resolve 'then' condition",
                    $this->read($if->getPosition()->getStart(), $thenChar->getEnd() + 10)
                );
            }

            // $if ? $then ...
            $elseChar = $this->firstNotEmpty($then->getPosition()->getEnd());
            if ($elseChar === null || $elseChar->getFound() !== self::COND_ELSE) {
                throw new SyntaxErrorException(
                    "Couldn't find ':' in ternary operator",
                    $this->read($if->getPosition()->getStart(), $then->getPosition()->getEnd() + 10)
                );
            }
        }

        $else = $this->nested($elseChar->getEnd());
        if ($else === null) {
            throw new SyntaxErrorException('Could\'t resolve "else" condition.');
        }

        return new Condition($if, $then, $else);
    }

    /**
     * Get then node
     *
     * @param int $offset
     * @return Node|null
     * @throws SyntaxErrorException
     */
    private function then(int $offset): ?Node
    {
        $next = $this->firstNotEmpty($offset);

        if ($next === null) {
            return null;
        }

        $container = $this->container($next->getStart());
        if ($container !== null) {
            $nested = $container;
        } else {
            // Some image or identity
            $identity = $this->identity($next->getStart());
            // There is may char ":" in 2 cases:
            // 1 ) next token is ImageSize
            // 2 ) next char is a special char of ternary token ( if ? then : else )
            try {
                $nested = $this->image($identity) ?? $identity;
            } catch (InvalidImageSizeException $e) {
                $nested = $identity;
            }
        }

        // There is may be some expression:
        // ${ `it is a string` | filter-expression(`filter-param`)
        return $this->expressionChain($nested) ?? $nested;
    }
}
