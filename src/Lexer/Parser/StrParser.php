<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Ast\Node\EscapedChar;
use DocxTemplate\Ast\Node\Str;
use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Lexer\Parser\Exception\EndNotFoundException;
use DocxTemplate\Contract\Ast\Node;

class StrParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?Node
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
                $last,
            );

            if ($nestedOrClose === null) {
                throw new EndNotFoundException(
                    "Couldn't find the end of element",
                    $this->read($open->getEnd(), $last + 20),
                );
            }

            if ($nestedOrClose->getFound() === self::STR_BRACE_ESCAPED) {
                // There is escaped string char. Continue
                $pos = new NodePosition($nestedOrClose->getStart(), $nestedOrClose->getLength());
                $nested[] = new EscapedChar($pos, $this->readBy($pos));
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

        return new Str($string, $this->readBy($string), ...$nested);
    }
}
