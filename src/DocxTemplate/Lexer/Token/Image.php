<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Token\Position\TokenPosition;

final class Image extends AbstractToken
{
    public const DELIMITER = ":";

    private ?ImageSize $size;

    public function __construct(string $name, TokenPosition $position, ?ImageSize $size = null)
    {
        parent::__construct($name, $position);
        $this->size = $size;
    }

    /**
     * Get image size
     * @return ImageSize|null
     */
    public function getSize(): ?ImageSize
    {
        return $this->size;
    }
}