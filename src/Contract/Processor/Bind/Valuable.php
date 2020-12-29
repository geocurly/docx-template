<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface Valuable
{
    /**
     * Identity of a bind
     * @return string
     */
    public function getId(): string;

    /**
     * Type of a bind
     * @return string
     */
    public function getType(): string;

    /**
     * Value of a bind
     * @return string
     */
    public function getValue(): string;
}
