<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Exception;


class SyntaxError extends LexerException
{
    private string $preview;

    public function __construct(string $preview, string $message = null)
    {
        parent::__construct($message ?? $this->message);
        $this->preview = $preview;
    }

    public function getPreview(): string
    {
        return $this->preview;
    }
}

