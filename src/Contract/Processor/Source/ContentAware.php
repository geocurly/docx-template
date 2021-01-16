<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Source;

interface ContentAware
{
    /**
     * Get file content
     * @return string
     */
    public function getContent(): string;
}
