<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

/** @codeCoverageIgnore  */
final class Decision
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
