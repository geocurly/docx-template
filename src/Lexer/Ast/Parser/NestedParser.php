<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Contract\Ast\AstNode;

class NestedParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?AstNode
    {
        $offset = $this->getOffset();
        $next = $this->firstNotEmpty($offset);
        if ($next === null) {
            return null;
        }

        switch ($next->getFound()) {
            case self::BLOCK_START:
                // ${...something
                $nested = $this->block($offset);
                break;
            case self::STR_BRACE;
                // `...something
                $nested = $this->string($offset);
                break;
            default:
                // Some identity
                $nested = $this->identity($offset);
                break;
        }

        // There is may be some expression:
        // ${ `it is a string` | filter-expression(`filter-param`)
        $expression = $this->expression($nested);
        while ($expression !== null) {
            $nested = $expression;
            $expression = $this->expression($nested);
        }

        return $nested;
    }
}
