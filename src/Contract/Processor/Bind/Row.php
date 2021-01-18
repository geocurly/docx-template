<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface Row
{
    /**
     * Get nested binds
     * @return iterable<Valuable>
     */
    public function getNested(): iterable;
}
