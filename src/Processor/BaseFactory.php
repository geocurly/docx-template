<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Contract\Processor\Bind\Bind;
use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Image;
use DocxTemplate\Contract\Processor\Bind\Table;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Exception\Processor\BindException;
use DocxTemplate\Processor\Process\Bind\Filter\DateFilter;

final class BaseFactory implements BindFactory
{
    /**
     * @var array<string, Valuable>
     */
    private array $valuable = [];

    /**
     * @var array<string, Filter>
     */
    private array $filters = [];

    /**
     * @var array<string, Image>
     */
    private array $images = [];

    /**
     * @var array<string, Table>
     */
    private array $tables = [];

    public function __construct(Bind ...$prototypes)
    {
        $date = new DateFilter('date');
        $this->filters[$date->getId()] = $date;

        foreach ($prototypes as $bind) {
            switch (true) {
                case $bind instanceof Filter:
                    $this->filters[$bind->getId()] = $bind;
                    break;
                case $bind instanceof Table:
                    $this->tables[$bind->getId()] = $bind;
                    break;
                case $bind instanceof Image:
                    $this->images[$bind->getId()] = $bind;
                    break;
                case $bind instanceof Valuable:
                    $this->valuable[$bind->getId()] = $bind;
                    break;
                default:
                    throw new BindException("Unknown bind instance: " . $bind::class);
            }
        }
    }

    /** @inheritDoc */
    public function valuable(string $name): ?Valuable
    {
        return isset($this->valuable[$name]) ? $this->valuable[$name]->clone(): null;
    }

    /** @inheritDoc */
    public function filter(string $name): ?Filter
    {
        return isset($this->filters[$name]) ? $this->filters[$name]->clone(): null;
    }

    /** @inheritDoc */
    public function image(string $name): ?Image
    {
        return isset($this->images[$name]) ? $this->images[$name]->clone(): null;
    }

    /** @inheritDoc */
    public function table(string $name): ?Table
    {
        return $this->tables[$name]?->clone();
    }
}
