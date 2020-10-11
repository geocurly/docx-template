<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Contract\Token\CallableInterface;
use DocxTemplate\Lexer\Contract\Token\TokenInterface;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Lexer\Token\Call;
use DocxTemplate\Lexer\Token\Filter;
use DocxTemplate\Lexer\Token\Image;
use DocxTemplate\Lexer\Token\Name;
use DocxTemplate\Lexer\Token\Position\TokenPosition;
use DocxTemplate\Lexer\Token\Scope;
use DocxTemplate\Lexer\Token\Str;
use DocxTemplate\Lexer\Token\Ternary;

class TokenParser
{
    private string $source;
    private ReaderInterface $reader;

    public function __construct(string $source, ReaderInterface $reader)
    {
        $this->source = $source;
        $this->reader = $reader;
    }

    /**
     * Make nested token
     *
     * @param int $position
     * @return TokenInterface|null
     * @throws SyntaxError
     */
    public function nested(int $position): ?TokenInterface
    {
        $next = $this->reader->firstNotEmpty([Str::BRACE, Scope::OPEN], $position);
        if ($next === null) {
            return null;
        }

        switch ($next[0]) {
            case Scope::OPEN:
                return $this->scope($position);
            case Str::BRACE;
                return $this->string($position);
        }

        throw new SyntaxError("Unclosed nested");
    }

    /**
     * Make string token
     *
     * @param int $start
     * @return TokenInterface|null
     * @throws SyntaxError
     */
    public function string(int $start): ?TokenInterface
    {
        $open = $this->reader->find(Str::BRACE, $start);
        if ($open === null) {
            return null;
        }

        $last = array_sum($open);
        $nested = [];
        while (true) {
            $nestedOrClose = $this->reader->findAny([Str::BRACE, Scope::OPEN], $last);
            if ($nestedOrClose === null) {
                throw new SyntaxError("Unclosed string");
            }

            if ($nestedOrClose[0] === Str::BRACE) {
                $string = new TokenPosition(
                    $this->source,
                    $open[0],
                    $nestedOrClose[2] + $nestedOrClose[1] - $open[0]
                );

                break;
            }

            if ($nestedOrClose[0] === Scope::OPEN) {
                $scope = $this->scope($nestedOrClose[1]);

                if ($scope === null) {
                    throw new SyntaxError("Unresolved scope");
                }

                $last = $scope->getPosition()->getEnd();
                $nested[] = $scope;
                continue;
            }

            throw new SyntaxError("Unknown nested start");
        }

        $content = $this->reader->read($string->getStart(), $string->getLength());
        return new Str(
            substr($content, 1, -1),
            $string,
            ...$nested
        );
    }

    public function scope(int $position): ?TokenInterface
    {
        $open = $this->reader->find(Scope::OPEN, $position);
        if ($open === null) {
            return null;
        }

        $lastPosition = array_sum($open);
        $first = $this->nested($lastPosition) ?? $this->name($lastPosition);
        if ($first === null) {
            throw new SyntaxError("Couldn't resolve nested construction in scope.");
        }

        $ternary = $this->ternary($first);
        if ($ternary !== null) {
            $next = $ternary;
        } else {
            $next = $first;
        }

        $nested[] = $next;
        $nextChar = $this->reader->nextNotEmpty($next->getPosition()->getEnd());
        while (true) {
            if ($nextChar === null) {
                throw new SyntaxError("Couldn't find end of scope.");
            }

            if ($nextChar[0] === Scope::CLOSE) {
                $closePosition = $nextChar[1] + $nextChar[2];
                break;
            }

            if ($ternary !== null) {
                throw new SyntaxError("Ternary operator must be single construction in scope.");
            }

            $next = $this->nested($nextChar[1]) ?? $this->name($nextChar[1]);
            if ($next === null) {
                throw new SyntaxError("Couldn't resolve nested construction in scope.");
            }

            $nested[] = $next;
            $nextChar = $this->reader->nextNotEmpty($next->getPosition()->getEnd());
        }

        $scopePosition = new TokenPosition($this->source, $open[0], $closePosition - $open[0]);
        $name = $this->reader->read(
            $open[0] + $open[1],
            $scopePosition->getEnd() - ($open[0] + $open[1]) - 1
        );

        return new Scope(trim($name), $scopePosition, ...$nested);
    }

    /**
     * Make ternary token
     *
     * @param TokenInterface $if condition token
     * @return TokenInterface|null
     * @throws SyntaxError
     */
    public function ternary(TokenInterface $if): ?TokenInterface
    {
        // $if ? ...
        $thenChar = $this->reader->firstNotEmpty([Ternary::THEN_CHAR], $if->getPosition()->getEnd());
        if ($thenChar === null) {
            return null;
        }

        $position = $thenChar[1] + $thenChar[2];
        $elseChar = $this->reader->firstNotEmpty([Ternary::ELSE_CHAR], $position);
        if ($elseChar !== null) {
            // There is ?:
            $then = $if;
        } else {
            // ${ $if ? `string` ...} or ${ $if ? ${scope} ... } or ${ $if ? name ... }
            $then = $this->nested($position) ?? $this->name($position);
            if ($then === null) {
                throw new SyntaxError('Could\'t resolve "then" condition.');
            }

            // $if ? $then ...
            $elseChar = $this->reader->firstNotEmpty([Ternary::ELSE_CHAR], $then->getPosition()->getEnd());
            if ($elseChar === null) {
                throw new SyntaxError('Could\'t find ":" in ternary operator.');
            }
        }

        $position = $elseChar[1] + $elseChar[2];
        $else = $this->nested($position) ?? $this->name($position);
        if ($else === null) {
            throw new SyntaxError('Could\'t resolve "else" condition.');
        }

        $ifPos = $if->getPosition();
        $elsePos = $else->getPosition();
        $ternaryPos = new TokenPosition(
            $this->source,
            $ifPos->getStart(),
            $elsePos->getEnd() - $ifPos->getStart()
        );

        return new Ternary(
            $this->reader->read($ternaryPos->getStart(), $ternaryPos->getLength()),
            $ternaryPos,
            $if,
            $then,
            $else
        );
    }

    /**
     * Parse simple name token
     * @param int $position
     * @return TokenInterface|CallableInterface|null
     * @throws SyntaxError
     */
    public function name(int $position): ?TokenInterface
    {
        $start = $this->reader->nextNotEmpty($position);
        if ($start === null) {
            throw new SyntaxError("Couldn't find start of the name");
        }

        $end = $this->reader->findAny(
            array_merge(
                ReaderInterface::EMPTY_CHARS,
                [Scope::CLOSE, Image::DELIMITER, Filter::PIPE, Call::ARGS_OPEN]
            ),
            $start[1] + $start[2]
        );

        if ($end === null) {
            throw new SyntaxError("Couldn't find end of the name");
        }


        [$startName, $lengthName] = [$start[1], $end[1] + $end[2] - $start[1] - 1];
        $content = $this->reader->read($startName, $lengthName);
        $name = strip_tags($content);
        if (preg_match('/^[\w_-]+$/', $name) !== 1) {
            throw new SyntaxError("Token name contains unavailable characters: $name");
        }

        if ($end[0] !== Call::ARGS_OPEN) {
            $namePosition = new TokenPosition($this->source, $startName, $lengthName);
            return new Name($content, $namePosition);
        }

        $next = $this->string(array_sum($end));
        if ($next === null) {
            throw new SyntaxError("Unknown call argument");
        } else {
            $args[] = $next;
        }

        while (true) {
            $char = $this->reader->nextNotEmpty($next->getPosition()->getEnd());
            if ($char[0] === Call::COMMA) {
                $next = $this->nested($char[1] + $char[2]);
                if ($next === null) {
                    throw new SyntaxError("Unknown call argument");
                } else {
                    $args[] = $next;
                }
            } elseif ($char[0] === Call::ARGS_CLOSE) {
                $end = $char;
                break;
            } else {
                throw new SyntaxError("Invalid call arguments");
            }
        }

        $lengthName = $end[1] + $end[2] - $startName;
        $namePosition = new TokenPosition($this->source, $startName, $lengthName);
        return new Call(
            $this->reader->read($startName, $lengthName),
            $namePosition,
            ...$args
        );
    }

    public function filter(TokenInterface $target): ?Filter
    {
        $filter = $this->filterElement($target);
        if ($filter === null) {
            return null;
        }

        $next = $this->filter($filter);
        if ($next !== null) {
            $filter->addNext($next);
        }

        return $filter;
    }

    private function filterElement(TokenInterface $target): ?Filter
    {
        $position = $target->getPosition();
        $pipe = $this->reader->nextNotEmpty($position->getEnd());
        // There is may be end of scope
        if ($pipe === null || $pipe[0] === Scope::CLOSE) {
            return null;
        }

        if ($pipe[0] !== Filter::PIPE) {
            throw new SyntaxError("Unexpected filter operator");
        }

        $name = $this->name($pipe[1] + $pipe[2]);
        if ($name === null) {
            throw new SyntaxError("Couldn't parse filter");
        }

        return new Filter($name);
    }
}