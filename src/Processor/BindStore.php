<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Ast\Node\FilterExpression;
use DocxTemplate\Contract\Ast\Identity;
use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Exception\Processor\BindException;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Processor\Process\Bind\Bind;

final class BindStore
{
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
                    $type = Identity::class;
                    break;
                case $bind instanceof Filter:
                    $type = FilterExpression::class;
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
        if ($node instanceof Identity) {
            $type = Identity::class;
        } else {
            $type = get_class($node);
        }

        $id = $node->getId();
        if (!isset($this->binds[$type][$id])) {
            throw new BindException("Unbound item: [type = $type, id = $id]");
        }

        return $this->binds[$type][$id];
    }
}
