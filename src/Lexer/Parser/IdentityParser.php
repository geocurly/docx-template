<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Ast\Node\Call;
use DocxTemplate\Ast\Node\Identity;
use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Lexer\Parser\Exception\ElementNotFoundException;
use DocxTemplate\Lexer\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Parser\Exception\UnexpectedCharactersException;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Lexer\Reader;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;

class IdentityParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?Node
    {
        $offset = $this->getOffset();
        
        $start = $this->firstNotEmpty($offset);
        if ($start === null) {
            throw new SyntaxErrorException("Couldn't find start of the identity");
        }

        $end = $this->findAnyOrEmpty(
            [
                self::BLOCK_END,
                self::IMAGE_SIZE_DELIMITER,
                self::FILTER_PIPE,
                self::PARAMS_OPEN,
                self::PARAMS_CLOSE,
                self::PARAMS_DELIMITER,
                self::COND_THEN,
                self::COND_ELSE,
            ],
            $start->getEnd()
        );

        if ($end === null) {
            throw new EndNotFoundException(
                "Couldn't find end of the identity",
                $this->read($start->getStart(), 20)
            );
        }


        $idPos = new NodePosition($start->getStart(), $end->getEnd() - $start->getStart() - 1);
        $content = $this->read($idPos->getStart(), $idPos->getLength());
        $template = implode(
            '|',
            array_map(
                fn($char) => preg_quote($char, '/'),
                array_merge(self::SPECIAL_CHARS, Reader::EMPTY_CHARS)
            )
        );
        if (preg_match("/^$template$/", $content) === 1) {
            throw new UnexpectedCharactersException("Identity contains unexpected characters", $content);
        }

        $id = new Identity($content, $idPos);
        if ($end->getFound() !== self::PARAMS_OPEN) {
            return $id;
        }

        $next = $this->nested($end->getEnd());
        if ($next === null) {
            $preview = $this->read($idPos->getStart(), $idPos->getLength() + 20);
            throw new ElementNotFoundException("Unknown call arguments", $preview);
        } else {
            $params[] = $next;
        }

        while (true) {
            $char = $this->firstNotEmpty($next->getPosition()->getEnd());
            if ($char->getFound() === self::PARAMS_DELIMITER) {
                $next = $this->nested($char->getEnd());
                if ($next === null) {
                    $preview = $this->read($idPos->getStart(), $idPos->getLength() + 20);
                    throw new ElementNotFoundException("Unknown call arguments", $preview);
                } else {
                    $params[] = $next;
                }
            } elseif ($char->getFound() === self::PARAMS_CLOSE) {
                $end = $char;
                break;
            } else {
                throw new UnexpectedCharactersException(
                    "Unexpected characters were found",
                    $this->read($idPos->getStart(), $idPos->getLength() + 20)
                );
            }
        }

        $callPos = new NodePosition($idPos->getStart(), $end->getEnd() - $idPos->getStart());
        return new Call($id, $callPos, ...$params);
    }
}
