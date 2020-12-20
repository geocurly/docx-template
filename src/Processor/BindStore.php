<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

class BindStore
{
    public const VARIABLE = 'var';

    private array $variables;

    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * Get bound variable
     * @param string $name
     * @return string|null
     */
    public function getVariable(string $name): ?string
    {
        return $this->variables[$name] ?? null;
    }
}
