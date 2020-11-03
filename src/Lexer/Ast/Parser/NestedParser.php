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
            case self::BLOCK_START[0]:
                // ${...something
                $nested = $this->block($next->getStart());
                break;
            case self::STR_BRACE;
                // `...something
                $nested = $this->string($next->getStart());
                break;
            default:
                // Some image or identity
                $nested = $this->image($next->getStart());
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
