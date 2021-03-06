<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface Valuable extends Bind
{
    /**
     * Value of a bind
     * @return string
     */
    public function getValue(): string;
}
