<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\Token\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Image implements TokenInterface
{
    public const DELIMITER = ":";

    private string $name;
    private TokenPosition $position;
    private ?ImageSize $size;

    public function __construct(string $name, TokenPosition $position, ?ImageSize $size = null)
    {
        $this->name = $name;
        $this->position = $position;
        $this->size = $size;
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