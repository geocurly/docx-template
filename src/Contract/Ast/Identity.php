<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Ast;

interface Identity extends Node
{
    public const VALUABLE = 'valuable';
    public const IMAGE = 'image';
    public const FILTER = 'filter';

    /**
     * Get node identity
     * @return string
     */
    public function getId(): string;

    /**
     * Get node type
     * @return string
     */
    public function getType(): string;

    /**
     * Get argument nodes
     * @return array<Node>
     */
    public function getArgs(): array;
}
