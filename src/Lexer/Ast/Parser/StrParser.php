<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\EscapedChar;
use DocxTemplate\Lexer\Ast\Node\Str;
use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Ast\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

class StrParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?AstNode
    {
        $offset = $this->getOffset();
        $open = $this->firstNotEmpty($offset);
        if ($open === null || $open->getFound() !== self::STR_BRACE) {
            return null;
        }

        $last = $open->getEnd();
        $nested = [];
        while (true) {
            $nestedOrClose = $this->findAny(
                [
                    self::STR_BRACE_ESCAPED,
                    self::STR_BRACE,
                    self::BLOCK_START_ESCAPED,
                    self::BLOCK_START,
                ],
                $last
            );

            if ($nestedOrClose === null) {
                throw new EndNotFoundException(
                    "Couldn't find the end of element",
                    $this->read($open->getEnd(), $last + 20)
                );
            }

            if ($nestedOrClose->getFound() === self::STR_BRACE_ESCAPED) {
                // There is escaped string char. Continue
                $nested[] = new EscapedChar(new NodePosition($nestedOrClose->getStart(), $nestedOrClose->getLength()));
                $last = $nestedOrClose->getEnd();
                continue;
            }

            if ($nestedOrClose->getFound() === self::STR_BRACE) {
                $string = new NodePosition($open->getStart(), $nestedOrClose->getEnd() - $open->getStart());
                break;
            }

            if (in_array($nestedOrClose->getFound(), [self::BLOCK_START_ESCAPED, self::BLOCK_START], true)) {
                $block = $this->block($nestedOrClose->getStart());
                $last = $block->getPosition()->getEnd();
                $nested[] = $block;
                continue;
            }
        }

        return new Str($string, ...$nested);
    }
}
