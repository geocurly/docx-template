<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\Token\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class ImageSize implements TokenInterface
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

    private string $name;
    private TokenPosition $position;
    private string $width;
    private string $height;
    private ?bool $ratio;

    public function __construct(
        string $name,
        TokenPosition $position,
        string $width,
        string $height,
        ?bool $ratio = null
    ) {
        $this->name = $name;
        $this->position = $position;
        $this->width = $width;
        $this->height = $height;
        $this->ratio = $ratio;
    }

    /** @inheritdoc  */
    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritdoc  */
    public function getPosition(): TokenPosition
    {
        return $this->position;
    }
}