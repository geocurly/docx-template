<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Contract\Lexer\Ast\Identity;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Exception\Processor\TemplateException;

final class BindStore
{
    public const TYPE_VARIABLE = 'Identity';
    public const TYPE_FILTER = 'FilterExpression';
    public const TYPE_IMAGE = 'Image';

    private array $binds;

    /**
     * BindStore constructor.
     * @param array $binds
     * @throws TemplateException
     */
    public function __construct(array $binds)
    {
        /** @var Valuable $bind */
        foreach ($binds as $bind) {
            switch (true) {
                case $bind instanceof Valuable:
                    $type = self::TYPE_VARIABLE;
                    break;
                default:
                    throw new TemplateException("Unknown bind type: " . get_class($bind));
            }

            $this->binds[$type][$bind->getId()] = $bind;
        }
    }

    /**
     * Get bind for node
     *
     * @param Identity $node
     * @return Valuable|null
     */
    public function get(Identity $node): ?Valuable
    {
        return $this->binds[$node->getType()][$node->getId()] ?? null;
    }
}
