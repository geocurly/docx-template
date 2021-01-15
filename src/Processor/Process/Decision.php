<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

/** @codeCoverageIgnore  */
class Decision /* implements Stringable */
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

    /** @inheritdoc  */
    public function __toString(): string
    {
        return $this->value;
    }
}
