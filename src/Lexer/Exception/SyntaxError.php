<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Exception;

use DocxTemplate\Lexer\Contract\ReaderInterface;

abstract class SyntaxError extends LexerException
{
    private string $preview;
    private ?int $errStart;
    private ?int $errEnd;

    public function __construct(string $preview, int $errStart = null, int $errEnd = null)
    {
        parent::__construct($this->message);
        $this->preview = $preview;
        $this->errStart = $errStart;
        $this->errEnd = $errEnd;
    }

    public function getPreview(): string
    {
        return $this->preview;
    }

    public function getErrStart(): ?int
    {
        return $this->errStart;
    }

    public function getErrEnd(): ?int
    {
        return $this->errEnd;
    }
}

