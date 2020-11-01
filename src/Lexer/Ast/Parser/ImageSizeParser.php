<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\SyntaxError;

class ImageSizeParser extends Parser
{
    public const CM = 'cm';
    public const MM = 'mm';
    public const IN = 'in';
    public const PT = 'pt';
    public const PX = 'px';
    public const PE = '%';
    public const EM = 'em';

    public const MEASURES = [
        self::CM,
        self::MM,
        self::IN,
        self::PT,
        self::PX,
        self::PE,
        self::EM,
    ];

    public const BOOLEAN = [
        'f' => false,
        'false' => false,
        't' => true,
        'true' => true,
    ];

    /** @inheritdoc  */
    public function parse(): ?AstNode
    {
        $offset = $this->getOffset();
        $end = $this->findAnyOrEmpty([self::BLOCK_END], $offset);
        if ($end === null) {
            throw new SyntaxError("Couldn't find the end of image size");
        }

        $points = implode('|', self::MEASURES);
        $boolean = implode('|', array_keys(self::BOOLEAN));
        $first = $this->firstNotEmpty($offset);
        switch (true) {
            // width=[width]:height=[height]:ratio=[ratio]
            // width=[width]:ratio=[ratio]:height=[height]
            // width=[width]:height=[height]
            case $first->getFound() === 'w';
                $pattern = [
                    "(?:width=(?P<w1>\d+(?:$points)?):height=(?P<h1>\d+(?:$points)?)(?::ratio=(?P<r1>$boolean))?)",
                    "(?:width=(?P<w2>\d+(?:$points)?):ratio=(?P<r2>$boolean)):height=(?P<h2>\d+(?:$points)?)",
                ];
                break;
            // height=[height]:width=[width]:ratio=[ratio]
            // height=[height]:ratio=[ratio]:width=[width]
            // height=[height]:width=[width]
            case $first->getFound() === 'h';
                $pattern = [
                    "(?:height=(?P<h1>\d+(?:$points)?):width=(?P<w1>\d+(?:$points)?)(?::ratio=(?P<r1>$boolean))?)",
                    "(?:height=(?P<h2>\d+(?:$points)?):ratio=(?P<r2>$boolean)):width=(?P<w2>\d+(?:$points)?)",
                ];
                break;
            // ratio=[ratio]:height=[height]:width=[width]
            // ratio=[ratio]:width=[width]:height=[height]
            case $first->getFound() === 'r';
                $pattern = [
                    "(?:ratio=(?P<r1>$boolean):height=(?P<h1>\d+(?:$points)?):width=(?P<w1>\d+(?:$points)?))",
                    "(?:ratio=(?P<r2>$boolean):width=(?P<w2>\d+(?:$points)?):height=(?P<h2>\d+(?:$points)?))",
                ];
                break;
            // size=[width]x[height] || size=[width]:[height]
            case $first->getFound() === 's';
                $pattern = ["size=(?P<w1>\d+(?:$points)?)(?:x|:)(?P<h1>\d+(?:$points)?)"];
                break;
            // [width]x[height] || [width]x[height]:[ratio]
            // [width]:[height] || [width]:[height]:[ratio]
            case ctype_digit($first->getFound());
                $pattern = ["(?P<w1>\d+(?:$points)?)(?:x|:)(?P<h1>\d+(?:$points)?)(?::(?P<r1>$boolean))?"];
                break;
            default:
                throw new SyntaxError("Invalid image size");
        }

        $template = '/^' . implode('|', $pattern) . '$/';

        $sizePos = new NodePosition($offset, $end->getStart() - $offset);
        $size = $this->read($sizePos->getStart(), $sizePos->getLength());
        if (preg_match($template, strip_tags($size), $match) !== 1) {
            throw new SyntaxError('Invalid image size');
        }

        for ($i = 1; $i <= 2; $i++) {
            [$width, $height, $ratio] = [$match["w$i"] ?? null, $match["h$i"] ?? null, $match["r$i"] ?? null];
            if (array_intersect([$width, $height], [null, '']) === []) {
                break;
            }
        }

        return new ImageSize(
            $sizePos,
            $width,
            $height,
            $ratio === null ? null : self::BOOLEAN[$ratio]
        );
    }
}
