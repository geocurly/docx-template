<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor;

use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Image;
use DocxTemplate\Contract\Processor\Bind\Table;
use DocxTemplate\Contract\Processor\Bind\Valuable;

interface BindFactory
{
    /**
     * Make variable bind by name
     *
     * @param string $name name of a bind
     * @return Valuable|null
     */
    public function valuable(string $name): ?Valuable;


    /**
     * Make filter bind by name
     *
     * @param string $name name of a bind
     * @return Filter|null
     */
    public function filter(string $name): ?Filter;

    /**
     * Make image bind by name
     *
     * @param string $name
     * @return Image|null
     */
    public function image(string $name): ?Image;

    /**
     * Make table bind by name
     *
     * @param string $name
     * @return Table|null
     */
    public function table(string $name): ?Table;
}
