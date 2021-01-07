<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor;

use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Valuable;

interface BindFactory
{
    /**
     * Make variable bind by name
     *
     * @param string $name name of a bind
     * @return Valuable
     */
    public function valuable(string $name): Valuable;


    /**
     * Make filter bind by name
     *
     * @param string $name name of a bind
     * @return Filter
     */
    public function filter(string $name): Filter;
}
