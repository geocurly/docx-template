<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process\Bind;

/** @codeCoverageIgnore  */
trait ParametersTrait /* implements ParametersAware */
{
    private array $params = [];

    /**
     * Get all parameters
     * @return array
     */
    final protected function getParams(): array
    {
        return $this->params;
    }

    /**
     * Set some params to entity
     * @param string ...$params
     */
    final public function setParams(string ...$params): void
    {
        $this->params = $params;
    }
}
