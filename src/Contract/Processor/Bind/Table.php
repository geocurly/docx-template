<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface Table extends Bind
{
    /**
     * Get table rows
     * @return iterable<Row>
     */
    public function getRows(): iterable;
}
