<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Contract\Ast\Identity;
use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Exception\Processor\BindException;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Processor\Process\Bind\Bind;

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
                case $bind instanceof Filter:
                    $type = self::TYPE_FILTER;
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
     * @return Bind
     * @throws BindException
     */
    public function get(Identity $node): \DocxTemplate\Contract\Processor\Bind\Bind
    {
        [$type, $id] = [$node->getType(), $node->getId()];
        if (!isset($this->binds[$type][$id])) {
            throw new BindException("Unbound item: [type = $type, id = $id]");
        }

        return $this->binds[$type][$id];
    }
}
