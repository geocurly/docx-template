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
use DocxTemplate\Lexer\Token\ImageSize;
use DocxTemplate\Lexer\Token\Name;
use DocxTemplate\Lexer\Token\Position\TokenPosition;
use DocxTemplate\Lexer\Token\Scope;
use DocxTemplate\Lexer\Token\Str;
use DocxTemplate\Lexer\Token\Ternary;

class TokenParser
{
    private ReaderInterface $reader;

    public function __construct(ReaderInterface $reader)
    {
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
        $next = $this->reader->firstNotEmpty($position, [Str::BRACE, Scope::OPEN]);
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
        $open = $this->reader->findAny([Str::BRACE], $start);
        if ($open === null) {
            return null;
        }

        $last = $open[1] + $open[2];
        $nested = [];
        while (true) {
            $nestedOrClose = $this->reader->findAny([Str::BRACE, Scope::OPEN], $last);
            if ($nestedOrClose === null) {
                throw new SyntaxError("Unclosed string");
            }

            if ($nestedOrClose[0] === Str::BRACE) {
                $string = new TokenPosition($open[1], $nestedOrClose[2] + $nestedOrClose[1] - $open[1]);
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
        $open = $this->reader->findAny([Scope::OPEN], $position);
        if ($open === null) {
            return null;
        }

        $lastPosition = $open[1] + $open[2];
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
        $nextChar = $this->reader->firstNotEmpty($next->getPosition()->getEnd());
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
            $nextChar = $this->reader->firstNotEmpty($next->getPosition()->getEnd());
        }

        $scopePosition = new TokenPosition($open[1],$closePosition - $open[1]);
        $name = $this->reader->read(
            $open[1] + $open[2],
            $scopePosition->getEnd() - ($open[1] + $open[2]) - 1
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
        $thenChar = $this->reader->firstNotEmpty($if->getPosition()->getEnd(), [Ternary::THEN_CHAR]);
        if ($thenChar === null) {
            return null;
        }

        $position = $thenChar[1] + $thenChar[2];
        $elseChar = $this->reader->firstNotEmpty($position, [Ternary::ELSE_CHAR]);
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
            $elseChar = $this->reader->firstNotEmpty($then->getPosition()->getEnd(), [Ternary::ELSE_CHAR]);
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
        $start = $this->reader->firstNotEmpty($position);
        if ($start === null) {
            throw new SyntaxError("Couldn't find start of the name");
        }

        $end = $this->reader->findAny(
            array_merge(
                ReaderInterface::EMPTY_CHARS,
                [Scope::CLOSE, Image::DELIMITER, Filter::PIPE, Call::ARGS_OPEN, Image::DELIMITER]
            ),
            $start[1] + $start[2]
        );

        if ($end === null) {
            throw new SyntaxError("Couldn't find end of the name");
        }


        [$startName, $lengthName] = [$start[1], $end[1] + $end[2] - $start[1] - 1];
        $content = $this->reader->read($startName, $lengthName);
        $name = strip_tags($content);
        if (preg_match('/^\s*[\w_-]+\s*$/', $name) !== 1) {
            throw new SyntaxError("Token name contains unavailable characters: $name");
        }

        if ($end[0] !== Call::ARGS_OPEN) {
            $namePosition = new TokenPosition($startName, $lengthName);
            return new Name($content, $namePosition);
        }

        $next = $this->string(array_sum($end));
        if ($next === null) {
            throw new SyntaxError("Unknown call argument");
        } else {
            $args[] = $next;
        }

        while (true) {
            $char = $this->reader->firstNotEmpty($next->getPosition()->getEnd());
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
        $namePosition = new TokenPosition($startName, $lengthName);
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
        $pipe = $this->reader->firstNotEmpty($position->getEnd());
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

    /**
     * ${search-image-pattern}
     * ${search-image-pattern:[width]:[height]:[ratio]}
     * ${search-image-pattern:[width]x[height]}
     * ${search-image-pattern:size=[width]x[height]}
     * ${search-image-pattern:width=[width]:height=[height]:ratio=false}
     *
     * Where:
     * [width] and [height] can be just numbers or numbers with measure,
     *         which supported by Word (cm, mm, in, pt, pc, px, %, em, ex)
     * [ratio] uses only for false, - or f to turn off respect aspect ration of image.
     *         By default template image size uses as ‘container’ size.
     *
     * @param int $position
     *
     * @return Image|null
     */
    public function image(int $position): ?TokenInterface
    {
        $name = $this->name($position);
        if ($name === null) {
            return null;
        }

        if ($name instanceof Call) {
            throw new SyntaxError("Image couldn't have an argument.");
        }

        $next = $this->reader->firstNotEmpty($name->getPosition()->getEnd());
        if ($next === null) {
            throw new SyntaxError("Unclosed image");
        }

        if ($next[0] === Scope::CLOSE) {
            // There is image without given size
            // This same as name token
            return new $name;
        }

        if ($next[0] === Image::DELIMITER) {
            $size = $this->imageSize($next[1] + $next[2]);
            if ($size === null) {
                throw new SyntaxError("Unknown image size");
            }
        } else {
            throw new SyntaxError("Unexpected symbol: {$next[0]}");
        }

        $nameStart = $name->getPosition()->getStart();
        $pos = new TokenPosition($nameStart, $size->getPosition()->getEnd() - $nameStart);
        return new Image(
            $this->reader->read($pos->getStart(), $pos->getLength()),
            $pos,
            $size
        );
    }

    /**
     * Try to find image size
     *
     * @param int $position
     * @return ImageSize|null
     */
    public function imageSize(int $position): ?ImageSize
    {
        $end = $this->reader->findAny(array_merge(ReaderInterface::EMPTY_CHARS, [Scope::CLOSE]), $position);
        if ($end === null) {
            throw new SyntaxError("Couldn't find the end of image size");
        }

        $points = implode('|', ImageSize::MEASURES);
        $boolean = implode('|', array_keys(ImageSize::BOOLEAN));
        $first = $this->reader->firstNotEmpty($position);
        switch (true) {
            // width=[width]:height=[height]:ratio=[ratio]
            // width=[width]:ratio=[ratio]:height=[height]
            // width=[width]:height=[height]
            case $first[0] === 'w';
                $pattern = [
                    "(?:width=(?P<w1>\d+(?:$points)?):height=(?P<h1>\d+(?:$points)?)(?::ratio=(?P<r1>$boolean))?)",
                    "(?:width=(?P<w2>\d+(?:$points)?):ratio=(?P<r2>$boolean)):height=(?P<h2>\d+(?:$points)?)",
                ];
                break;
            // height=[height]:width=[width]:ratio=[ratio]
            // height=[height]:ratio=[ratio]:width=[width]
            // height=[height]:width=[width]
            case $first[0] === 'h';
                $pattern = [
                    "(?:height=(?P<h1>\d+(?:$points)?):width=(?P<w1>\d+(?:$points)?)(?::ratio=(?P<r1>$boolean))?)",
                    "(?:height=(?P<h2>\d+(?:$points)?):ratio=(?P<r2>$boolean)):width=(?P<w2>\d+(?:$points)?)",
                ];
                break;
            // ratio=[ratio]:height=[height]:width=[width]
            // ratio=[ratio]:width=[width]:height=[height]
            case $first[0] === 'r';
                $pattern = [
                    "(?:ratio=(?P<r1>$boolean):height=(?P<h1>\d+(?:$points)?):width=(?P<w1>\d+(?:$points)?))",
                    "(?:ratio=(?P<r2>$boolean):width=(?P<w2>\d+(?:$points)?):height=(?P<h2>\d+(?:$points)?))",
                ];
                break;
            // size=[width]x[height] || size=[width]:[height]
            case $first[0] === 's';
                $pattern = ["size=(?P<w1>\d+(?:$points)?)(?:x|:)(?P<h1>\d+(?:$points)?)"];
                break;
            // [width]x[height] || [width]x[height]:[ratio]
            // [width]:[height] || [width]:[height]:[ratio]
            case ctype_digit($first[0]);
                $pattern = ["(?P<w1>\d+(?:$points)?)(?:x|:)(?P<h1>\d+(?:$points)?)(?::(?P<r1>$boolean))?"];
                break;
            default:
                throw new SyntaxError("Invalid image size");
        }

        $template = '/^' . implode('|', $pattern) . '$/';

        $size = $this->reader->read($position, $end[1] - $position);
        if (preg_match($template, $size, $match) !== 1) {
            throw new SyntaxError('Invalid image size');
        }

        for ($i = 1; $i <= 2; $i++) {
            [$width, $height, $ratio] = [$match["w$i"] ?? null, $match["h$i"] ?? null, $match["r$i"] ?? null];
            if (array_intersect([$width, $height], [null, '']) === []) {
                break;
            }
        }

        return new ImageSize(
            $size,
            new TokenPosition($position, $end[1] - $position),
            $width,
            $height,
            $ratio === null ? null : ImageSize::BOOLEAN[$ratio]
        );
    }
}