<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface ArgumentAware
{
    /**
     * Set some arguments to entity
     * @param mixed ...$args
     */
    public function setArguments(...$args): void;

    /**
     * Get arguments
     * @return array
     */
    public function getArguments(): array;
}