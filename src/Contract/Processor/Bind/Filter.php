<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface Filter extends Bind
{
    /**
     * FilterBind some valuable entity
     * @param mixed $entity
     */
    public function filter($entity);
}
