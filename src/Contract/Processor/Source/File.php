<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Source;

interface File
{
    /**
     * Get file content
     * @return string
     */
    public function getContent(): string;

    /**
     * Get file URL
     * @return string
     */
    public function getUrl(): string;
}
