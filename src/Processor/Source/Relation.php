<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

/** @codeCoverageIgnore  */
final class Relation
{
    private string $id;
    private string $target;
    private string $type;
    private string $url;

    public function __construct(string $url, string $id, string $target, string $type)
    {
        $this->id = $id;
        $this->target = $target;
        $this->type = $type;
        $this->url = $url;
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

    /**
     * Original url of relation
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
