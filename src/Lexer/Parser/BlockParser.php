<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Ast\Node\Block;
use DocxTemplate\Ast\Node\EscapedBlock;
use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Lexer\Parser\Exception\EndNotFoundException;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Lexer\SyntaxError;

class BlockParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?Node
    {
        $offset = $this->getOffset();
        $open = $this->findAny([self::BLOCK_START, self::BLOCK_START_ESCAPED], $offset);
        if ($open === null) {
            return null;
        }

        $first = $this->nested($open->getEnd());
        if ($first === null) {
            throw new SyntaxError("Couldn't resolve nested construction in block");
        }

        $condition = $this->condition($first);
        $next = $condition ?? $first;

        $nested[] = $next;
        $nextChar = $this->firstNotEmpty($next->getPosition()->getEnd());
        while (true) {
            if ($nextChar === null) {
                throw new EndNotFoundException(
                    "Couldn't find end of block",
                    $this->read($open->getStart(), $next->getPosition()->getEnd())
                );
            }

            if ($nextChar->getFound() === self::BLOCK_END) {
                $close = $nextChar->getEnd();
                break;
            }

            if ($condition !== null) {
                $preview = $this->read($open->getStart(), $nextChar->getEnd());
                throw new SyntaxError("Condition must be single construction in block", $preview);
            }

            $next = $this->nested($nextChar->getStart());
            if ($next === null) {
                throw new SyntaxError("Couldn't resolve nested construction in block");
            }

            $nested[] = $next;
            $nextChar = $this->firstNotEmpty($next->getPosition()->getEnd());
        }

        $position = new NodePosition($open->getStart(),$close - $open->getStart());
        return $open->getFound() === self::BLOCK_START_ESCAPED ?
            new EscapedBlock($position, $this->readBy($position), ...$nested) :
            new Block($position, $this->readBy($position), ...$nested);
    }
}
