<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Condition;
use DocxTemplate\Lexer\Ast\Node\FilterExpression;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\SyntaxError;

class ConditionParser extends Parser
{
    private AstNode $if;

    public function __construct(ReaderInterface $reader, AstNode $if)
    {
        parent::__construct($reader, $if->getPosition()->getEnd());
        $this->if = $if;
    }

    /** @inheritdoc  */
    public function parse(): ?AstNode
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
            $then = $this->nested($thenChar->getEnd());
            if ($then === null) {
                throw new SyntaxError('Could\'t resolve "then" condition');
            } elseif ($then instanceof FilterExpression) {
                throw new SyntaxError(
                    "Filter expression doesn't support inside condition." .
                    "Please wrap it like '\${ var | filter }'"
                );
            }

            // $if ? $then ...
            $elseChar = $this->firstNotEmpty($then->getPosition()->getEnd());
            if ($elseChar === null || $elseChar->getFound() !== self::COND_ELSE) {
                throw new SyntaxError('Could\'t find ":" in ternary operator.');
            }
        }

        $else = $this->nested($elseChar->getEnd());
        if ($else === null) {
            throw new SyntaxError('Could\'t resolve "else" condition.');
        } elseif ($then instanceof FilterExpression) {
            throw new SyntaxError(
                "Filter expression doesn't support inside condition." .
                "Please wrap it like '\${ var | filter }'"
            );
        }

        return new Condition($if, $then, $else);
    }
}