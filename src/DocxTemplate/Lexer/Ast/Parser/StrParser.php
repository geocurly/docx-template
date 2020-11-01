<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Str;
use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\SyntaxError;

class StrParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?AstNode
    {
        $offset = $this->getOffset();
        $open = $this->findAny([self::STR_BRACE], $offset);
        if ($open === null) {
            return null;
        }

        $last = $open->getEnd();
        $nested = [];
        while (true) {
            $nestedOrClose = $this->findAny([self::STR_BRACE, self::BLOCK_START], $last);
            if ($nestedOrClose === null) {
                throw new SyntaxError("Unclosed string");
            }

            if ($nestedOrClose->getFound() === self::STR_BRACE) {
                $string = new NodePosition($open->getStart(), $nestedOrClose->getEnd() - $open->getStart());
                break;
            }

            if ($nestedOrClose->getFound() === self::BLOCK_START) {
                $scope = $this->block($nestedOrClose->getStart());

                if ($scope === null) {
                    throw new SyntaxError("Unresolved block");
                }

                $last = $scope->getPosition()->getEnd();
                $nested[] = $scope;
                continue;
            }

            throw new SyntaxError("Unknown start of nested node");
        }

        return new Str($string, ...$nested);
    }
}
