<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Source;

/**
 * @template SourceFilePath of string
 * @template SourceFileContent of string
 */
interface Source
{
    /**
     * Get files content to process.
     *
     * @return iterable<SourceFilePath, RelationContainer>
     */
    public function getPreparedFiles(): iterable;

    /**
     * Get leftover files after processing.
     *
     * @return iterable<SourceFilePath, SourceFileContent>
     */
    public function getLeftoverFiles(): iterable;
}
