<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Contract\Ast\Node;

class NestedParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?Node
    {
        $offset = $this->getOffset();
        $next = $this->firstNotEmpty($offset);

        $container = $this->container($next->getStart());
        if ($container !== null) {
            $nested = $container;
        } else {
            // Some image or identity
            $identity = $this->identity($next->getStart());
            $nested = $this->image($identity) ?? $identity;
        }

        // There is may be some expression:
        // ${ `it is a string` | filter-expression(`filter-param`)
        return $this->expressionChain($nested) ?? $nested;
    }
}
