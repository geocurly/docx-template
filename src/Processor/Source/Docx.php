<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DocxTemplate\Exception\Processor\ResourceOpenException;
use ZipArchive;

final class Docx
{
    private const CONTENT_TYPE_XML = '[Content_Types].xml';

    private ZipArchive $zip;
    private ContentTypes $types;
    /** @var Relations[] */
    private array $relations = [];
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

        $this->types = new ContentTypes($this->get(self::CONTENT_TYPE_XML));

        for( $i = 0; $i < $this->zip->numFiles; $i++ ) {
            $name = $this->zip->getNameIndex($i, ZipArchive::FL_UNCHANGED);
            $this->files[$name] = null;
        }
    }

    /**
     * Get docx relation files
     * @return Relations
     * @throws ResourceOpenException
     */
     public function getDocumentRelations(): Relations
    {
        $document = $this->types->getDocumentPath();
        $name = $this->getRelationsName($document);
        return $this->relations[$document] ??= new Relations(
            $document,
            $name,
            $this->get($name)
        );
    }

    /**
     * Get relation file name by owner file
     * @param string $owner
     * @return string
     */
    private function getRelationsName(string $owner): string
    {
        return "word/_rels/" . basename($owner) . ".rels";
    }

    /**
     * Get relation files
     * @param string $owner
     * @return Relations
     */
    public function getRelation(string $owner): Relations
    {
        if (isset($this->relations[$owner])) {
            return $this->relations[$owner];
        }

        $file = $owner . 'rels';
        try {
            $this->relations[$owner] = new Relations($owner, $this->getRelationsName($owner), $this->get($file));
        } catch (ResourceOpenException $exception) {
            $this->relations[$owner] = new Relations($owner, $this->getRelationsName($owner));
        }

        return $this->relations[$owner];
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
