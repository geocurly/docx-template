<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface Bind
{
    /**
     * Identity of a bind
     * @return string
     */
    public function getId(): string;

    /**
     * Set some params to entity
     * @param string ...$params
     */
    public function setParams(string ...$params): void;

    /**
     * Clone bind to new instance
     * @return static
     */
    public function clone(): static;
}
