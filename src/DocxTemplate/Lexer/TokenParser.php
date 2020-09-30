<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Contract\TokenInterface;
use DocxTemplate\Lexer\Exception\SyntaxError;
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
     * @param ReaderInterface $reader
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
     * @param int $position
     * @return TokenInterface|null
     */
    public function string(int $position): ?TokenInterface
    {

    }

    public function scope(int $position): ?TokenInterface
    {
        $open = $this->reader->find(Scope::OPEN, $position);
        if ($open === null) {
            return null;
        }

        $lastPosition = array_sum($open);
        $nested = $this->nested($lastPosition) ?? $this->name($lastPosition);
        if ($nested === null) {
            throw new SyntaxError("Couldn't resolve nested construction in scope.");
        }

        $next = $this->ternary($nested) ?? $nested;
        $filter = $this->filter($next);

        $lastPosition = ($filter ?? $next)->getPosition()->getEnd();

        $close = $this->reader->find(Scope::CLOSE, $lastPosition);
        if ($close === null) {
            throw new SyntaxError("Couldn't find end of scope.");
        }

        $scopePosition = new TokenPosition($this->source, $open[0], $close[0] + $close[1] - $open[0]);
        return new Scope(
            $this->reader->read($scopePosition->getStart(), $scopePosition->getLength()),
            $scopePosition,
            ...array_filter([$next, $filter])
        );
    }

    /**
     * Make ternary token
     *
     * @param ReaderInterface $reader
     * @param TokenInterface $if condition token
     * @return TokenInterface|null
     */
    public function ternary(TokenInterface $if): ?TokenInterface
    {
        // $if ? ...
        $thenChar = $this->reader->firstNotEmpty([Ternary::THEN_CHAR], $if->getPosition()->getEnd());
        if ($thenChar === null) {
            return null;
        }

        $position = $thenChar[1] + $thenChar[2];
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

        $position = $thenChar[1] + $thenChar[2];
        $else = $this->nested($position) ?? $this->name($position);
        if ($else === null) {
            throw new SyntaxError('Could\'t resolve "else" condition.');
        }

        return new Ternary(new TokenPosition(), $if, $then, $else);
    }

    /**
     * Parse simple name token
     * @param int $position
     * @return TokenInterface|null
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
                [Scope::CLOSE, Image::DELIMITER, Filter::PIPE]
            ),
            $start[1] + $start[2]
        );

        if ($end === null) {
            throw new SyntaxError("Couldn't find end of the name");
        }

        $namePosition = new TokenPosition($this->source, $start[1], $end[1] + $end[2] - $start[1] - 1);
        $name = strip_tags($this->reader->read($namePosition->getStart(), $namePosition->getLength()));
        if (preg_match('/^[\w_-]+$/', $name) !== 1) {
            throw new SyntaxError("Token name contains unavailable characters: $name");
        }

        return new Name($name, $namePosition);
    }

    public function filter(TokenInterface $target): ?TokenInterface
    {

    }
}