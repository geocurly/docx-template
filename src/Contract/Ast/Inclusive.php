<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Ast;

interface Inclusive extends Node
{
    /**
     * Get node content
     * @return string
     */
    public function getContent(): string;
}
