<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\SyntaxError;

class IdentityParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?AstNode
    {
        $offset = $this->getOffset();
        
        $start = $this->firstNotEmpty($offset);
        if ($start === null) {
            throw new SyntaxError("Couldn't find start of the name");
        }

        $end = $this->findAnyOrEmpty(
            [self::BLOCK_END, self::IMAGE_SIZE_DELIMITER, self::FILTER_PIPE, self::PARAMS_OPEN],
            $start->getEnd()
        );

        if ($end === null) {
            throw new SyntaxError("Couldn't find end of the name");
        }


        $idPos = new NodePosition($start->getStart(), $end->getEnd() - $start->getStart() - 1);
        $content = $this->read($idPos->getStart(), $idPos->getLength());
        $name = strip_tags($content);
        if (preg_match('/^\s*[\w_-]+\s*$/', $name) !== 1) {
            throw new SyntaxError("Token name contains unavailable characters: $name");
        }

        $id = new Identity($name, $idPos);
        if ($end->getFound() !== self::PARAMS_OPEN) {
            return $id;
        }

        $next = $this->string($end->getEnd());
        if ($next === null) {
            throw new SyntaxError("Unknown call argument");
        } else {
            $params[] = $next;
        }

        while (true) {
            $char = $this->firstNotEmpty($next->getPosition()->getEnd());
            if ($char->getFound() === self::PARAMS_DELIMITER) {
                $next = $this->nested($char->getEnd());
                if ($next === null) {
                    throw new SyntaxError("Unknown call argument");
                } else {
                    $params[] = $next;
                }
            } elseif ($char->getFound() === self::PARAMS_CLOSE) {
                $end = $char;
                break;
            } else {
                throw new SyntaxError("Invalid call arguments");
            }
        }

        $callPos = new NodePosition($idPos->getStart(), $end->getEnd() - $idPos->getStart());
        return new Call($id, $callPos, ...$params);
    }
}