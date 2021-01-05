<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Common;

use DocxTemplate\Processor\Process\Bind\Bind;
use DocxTemplate\Processor\Process\Bind\Filter;

trait BindTrait
{
    public static function filterBind(string $id, callable $function): Filter
    {
        return new class($id, $function) extends Filter {
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

    public static function valuableBind(string $id, callable $function): Bind
    {
        return new class($id, $function) extends Bind {
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
}
