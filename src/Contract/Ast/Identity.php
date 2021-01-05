<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Ast;

interface Identity extends Node
{
    /**
     * Get node identity
     * @return string
     */
    public function getId(): string;
}
