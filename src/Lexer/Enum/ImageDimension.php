<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Enum;

final class ImageDimension
{
    public const CM = 'cm';
    public const MM = 'mm';
    public const IN = 'in';
    public const PT = 'pt';
    public const PX = 'px';
    public const PE = '%';
    public const EM = 'em';

    /** @var string[] All available dimensions */
    public const DIMENSIONS = [
        self::CM,
        self::MM,
        self::IN,
        self::PT,
        self::PX,
        self::PE,
        self::EM,
    ];
}
