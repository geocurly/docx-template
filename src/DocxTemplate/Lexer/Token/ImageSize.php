<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Token\Position\TokenPosition;

final class ImageSize extends AbstractToken
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
        parent::__construct($name, $position);
        $this->width = $width;
        $this->height = $height;
        $this->ratio = $ratio;
    }

    /**
     * Image width
     * @return string
     */
    public function getWidth(): string
    {
        return $this->width;
    }

    /**
     * Image height
     * @return string
     */
    public function getHeight(): string
    {
        return $this->height;
    }

    /**
     * Is save ratio
     * @return bool|null
     */
    public function getRatio(): ?bool
    {
        return $this->ratio;
    }
}