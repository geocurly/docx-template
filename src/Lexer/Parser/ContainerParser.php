<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Contract\Ast\Node;

class ContainerParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?Node
    {
        $next = $this->firstNotEmpty($this->getOffset());
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

                return null;
            default:
                return null;
        }
    }
}
