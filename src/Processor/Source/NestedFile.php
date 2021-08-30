<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DocxTemplate\Contract\Processor\Source\Relation;
use DocxTemplate\Contract\Processor\Source\RelationContainer;

/** @codeCoverageIgnore  */
final class NestedFile implements RelationContainer
{
    public function __construct(
        private string $url,
        private string $source,
        private Relations $relations,
        private ContentTypes $types
    ) {
    }

    /** @inheritdoc  */
    public function getContent(): string
    {
        return $this->source;
    }

    /** @inheritdoc  */
    public function add(Relation $relation): void
    {
        $this->relations->add($relation);
        $this->types->add(
            $relation->getTarget(),
            $relation->getMime(),
        );
    }

    /** @inheritdoc  */
    public function getNextRelationId(): string
    {
        return $this->relations->getNextRelationId();
    }
}