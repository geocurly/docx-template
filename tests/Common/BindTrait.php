<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Common;

use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Image;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Processor\Process\Bind\Filter\Date;
use DocxTemplate\Processor\Process\Bind\ImageBind;
use DocxTemplate\Processor\Process\Bind\ValuableBind;
use DocxTemplate\Processor\Process\Bind\FilterBind;

trait BindTrait
{
    /**
     * @template name of string
     * @template factory of callable
     * @template size of array
     *
     * @param list<name, factory> $valuables
     * @param list<name, array<factory, size>> $images
     * @param list<name, factory> $filters
     * @return BindFactory
     */
    public static function mockBindFactory(array $valuables = [], array $images = [], array $filters = []): BindFactory
    {
        return new class($valuables, $images, $filters) implements BindFactory
        {
            private array $valuables;
            private array $filters;
            private array $images;

            public function __construct(array $valuables, array $images, array $filters)
            {
                $this->valuables = $valuables;
                $this->filters = $filters;
                $this->images = $images;
            }

            /** @inheritdoc  */
            public function valuable(string $name): ?Valuable
            {
                if (!isset($this->valuables[$name])) {
                    return null;
                }

                return new class($name, $this->valuables[$name]) extends ValuableBind {
                    private string $id;
                    private $function;

                    public function __construct(string $id, callable $function)
                    {
                        $this->id = $id;
                        $this->function = $function;
                    }


                    public function getId(): string
                    {
                        return $this->id;
                    }

                    public function getValue(): string
                    {
                        $call = $this->function;
                        return $call(...$this->getParams());
                    }
                };
            }

            /** @inheritdoc  */
            public function filter(string $name): ?Filter
            {
                if (!isset($this->filters[$name])) {
                    switch ($name) {
                        case 'date':
                            return new Date($name);
                    }

                    return null;
                }

                return new class($name, $this->filters[$name]) extends FilterBind {
                    private string $id;
                    private $function;

                    public function __construct(string $id, callable $function)
                    {
                        $this->id = $id;
                        $this->function = $function;
                    }

                    public function getId(): string
                    {
                        return $this->id;
                    }

                    public function filter($entity)
                    {
                        $call = $this->function;
                        return $call($entity, ...$this->getParams());
                    }
                };
            }

            public function image(string $name): ?Image
            {
                if (!isset($this->images[$name][0])) {
                    return null;
                }

                $call = $this->images[$name][0];
                $size = $this->images[$name][1] ?? [null, null, null];
                return new class($name, $call, ...$size) extends ImageBind {

                    private string $id;
                    private $fn;

                    public function __construct(string $id, callable $fn, ?array $w, ?array $h, ?bool $r)
                    {
                        $this->id = $id;
                        $this->fn = $fn;
                        if ($w !== null) {
                            $this->setWidth(...$w);
                        }

                        if ($h !== null) {
                            $this->setHeight(...$h);
                        }

                        if ($r !== null) {
                            $this->setSaveRatio($r);
                        }
                    }

                    public function getId(): string
                    {
                        return $this->id;
                    }

                    public function getValue(): string
                    {
                        $fn = $this->fn;
                        return $fn(...$this->getParams());
                    }
                };
            }
        };
    }
}
