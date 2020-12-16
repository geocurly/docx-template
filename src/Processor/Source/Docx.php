<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DocxTemplate\Processor\Exception\ResourceOpenException;
use DocxTemplate\Processor\Exception\TemplateException;
use ZipArchive;

class Docx
{
    private ZipArchive $zip;
    private array $files = [];

    /**
     * Docx constructor.
     * @param string $source docx file name
     * @throws ResourceOpenException
     */
    public function __construct(string $source)
    {
        $this->zip = new ZipArchive();
        if ($this->zip->open($source, ZipArchive::CHECKCONS) === false) {
            throw new ResourceOpenException("Couldn't open docx document");
        }

        for( $i = 0; $i < $this->zip->numFiles; $i++ ) {
            $name = $this->zip->getNameIndex($i, ZipArchive::FL_UNCHANGED);
            $this->files[$name] = null;
        }
    }

    /**
     * Get docx relation files
     * @return iterable
     * @throws TemplateException
     */
    public function getRelations(): iterable
    {
        foreach ($this->files ?? [] as $name => $_) {
            if (strpos($name, 'word/_rels/') === false) {
                continue;
            }

            $main = substr($name, 11, -5);
            if (!array_key_exists("word/$main", $this->files)) {
                throw new TemplateException("Unknown main part $main for relation $name");
            }

            yield "word/$main" => $name;
        }
    }

    /**
     * Get file content
     *
     * @param string $name
     * @return string
     * @throws ResourceOpenException
     */
    public function get(string $name): string
    {
        $file = $this->zip->getFromName($name, 0, ZipArchive::FL_UNCHANGED);
        if ($file === false) {
            throw new ResourceOpenException("Couldn't open nested file: $name");
        }

        unset($this->files[$name]);
        return $file;
    }

    public function flush(): iterable
    {
        foreach ($this->files as $name => $_) {
            yield $name => $this->get($name);
        }

        $this->zip->close();
    }
}
