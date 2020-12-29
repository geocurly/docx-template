<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface ParametersAware
{
    /**
     * Set some params to entity
     * @param string ...$params
     */
    public function setParams(string ...$params): void;
}
