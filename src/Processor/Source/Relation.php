<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

/** @codeCoverageIgnore  */
class Relation
{
    private string $id;
    private string $target;
    private string $type;

    public function __construct(string $id, string $target, string $type)
    {
        $this->id = $id;
        $this->target = $target;
        $this->type = $type;
    }

    /**
     * Get relation identity
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get relation target
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Get relation type
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
