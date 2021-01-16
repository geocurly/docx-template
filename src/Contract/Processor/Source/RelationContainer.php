<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Source;

interface RelationContainer extends ContentAware
{
    /**
     * Add relation to file
     * @param Relation $relation
     */
    public function add(Relation $relation): void;

    /**
     * Get next relation identity
     * @return string
     */
    public function getNextRelationId(): string;
}
