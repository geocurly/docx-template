<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Parser;

use DocxTemplate\Ast\Node\ImageSize;
use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Lexer\Enum\ImageDimension;
use DocxTemplate\Lexer\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Parser\Exception\InvalidImageSizeException;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Ast\Identity as IdentityInterface;
use DocxTemplate\Contract\Lexer\Reader;

class ImageSizeParser extends Parser
{
    public const BOOLEAN = [
        'f' => false,
        'false' => false,
        't' => true,
        'true' => true,
    ];

    public function __construct(Reader $reader, private IdentityInterface $identity)
    {
        parent::__construct($reader, $identity->getPosition()->getEnd());
    }

    /** @inheritdoc  */
    public function parse(): ?Node
    {
        $next = $this->firstNotEmpty($this->getOffset());
        if ($next === null) {
            return null;
        }

        if ($next->getFound() !== self::IMAGE_SIZE_DELIMITER) {
            return null;
        }

        $sizeStartChar = $this->firstNotEmpty($next->getEnd());
        if ($sizeStartChar === null) {
            return null;
        }

        $offset = $sizeStartChar->getStart();
        $end = $this->findAnyOrEmpty([self::BLOCK_END, self::PARAMS_CLOSE, self::PARAMS_DELIMITER], $offset);
        if ($end === null) {
            throw new EndNotFoundException("Couldn't find the end of element", $this->getPreview(20));
        }

        // TODO parse without regex
        $points = implode('|', ImageDimension::DIMENSIONS);
        $boolean = implode('|', array_keys(self::BOOLEAN));
        $first = $this->firstNotEmpty($offset);
        switch (true) {
            // width=[width]:height=[height]:ratio=[ratio]
            // width=[width]:ratio=[ratio]:height=[height]
            // width=[width]:height=[height]
            case $first->getFound() === 'w';
                $pattern = [
                    "^(?:width=(?P<w1>\d+(?:$points)?):height=(?P<h1>\d+(?:$points)?)(?::ratio=(?P<r1>$boolean))?)$",
                    "^(?:width=(?P<w2>\d+(?:$points)?):ratio=(?P<r2>$boolean)):height=(?P<h2>\d+(?:$points)?)$",
                ];
                break;
            // height=[height]:width=[width]:ratio=[ratio]
            // height=[height]:ratio=[ratio]:width=[width]
            // height=[height]:width=[width]
            case $first->getFound() === 'h';
                $pattern = [
                    "^(?:height=(?P<h1>\d+(?:$points)?):width=(?P<w1>\d+(?:$points)?)(?::ratio=(?P<r1>$boolean))?)$",
                    "^(?:height=(?P<h2>\d+(?:$points)?):ratio=(?P<r2>$boolean)):width=(?P<w2>\d+(?:$points)?)$",
                ];
                break;
            // ratio=[ratio]:height=[height]:width=[width]
            // ratio=[ratio]:width=[width]:height=[height]
            case $first->getFound() === 'r';
                $pattern = [
                    "^(?:ratio=(?P<r1>$boolean):height=(?P<h1>\d+(?:$points)?):width=(?P<w1>\d+(?:$points)?))$",
                    "^(?:ratio=(?P<r2>$boolean):width=(?P<w2>\d+(?:$points)?):height=(?P<h2>\d+(?:$points)?))$",
                ];
                break;
            // size=[width]x[height] || size=[width]:[height]
            case $first->getFound() === 's';
                $pattern = ["^size=(?P<w1>\d+(?:$points)?)(?:x|:)(?P<h1>\d+(?:$points)?)$"];
                break;
            // [width]x[height] || [width]x[height]:[ratio]
            // [width]:[height] || [width]:[height]:[ratio]
            case ctype_digit($first->getFound());
                $pattern = ["^(?P<w1>\d+(?:$points)?)(?:x|:)(?P<h1>\d+(?:$points)?)(?::(?P<r1>$boolean))?$"];
                break;
            default:
                throw new InvalidImageSizeException('Invalid image size', $this->getPreview(20));
        }

        $template = '/' . implode('|', $pattern) . '/';

        $sizePos = new NodePosition($offset, $end->getStart() - $offset);
        $size = $this->read($sizePos->getStart(), $sizePos->getLength());
        if (preg_match($template, $size, $match) !== 1) {
            throw new InvalidImageSizeException('Invalid image size', $this->getPreview(20));
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

    /**
     * Get content for preview
     * @param int $end
     * @return string
     */
    private function getPreview(int $end): string
    {
        $pos = $this->identity->getPosition();
        return $this->read($pos->getStart(), $pos->getEnd() + $end);
    }
}
