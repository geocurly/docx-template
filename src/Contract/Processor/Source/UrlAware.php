<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Source;

interface UrlAware
{
    /**
     * Get file URL
     * @return string
     */
    public function getUrl(): string;
}
