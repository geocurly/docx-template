<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Source;

interface Relation extends UrlAware
{
    /**
     * Get identity of relation
     * @return string
     */
    public function getId(): string;

    /**
     * Get type of relation
     * @return string
     */
    public function getType(): string;

    /**
     * Get mime of relation
     * @return string
     */
    public function getMime(): string;

    /**
     * Get target url in source
     * @return string
     */
    public function getTarget(): string;

    /**
     * Get relation content
     * @return string
     */
    public function getContent(): string;
}
