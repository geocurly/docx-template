<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DocxTemplate\Contract\Processor\Source\Source;
use DocxTemplate\Exception\Processor\ResourceOpenException;
use ZipArchive;

class Docx implements Source
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
     private function getRelation(string $owner): Relations
    {
        if (isset($this->relations[$owner])) {
            return $this->relations[$owner];
        }

        $file = $this->getRelationsName($owner);
        try {
            $this->relations[$owner] = new Relations($owner, $file, $this->get($file));
        } catch (ResourceOpenException $exception) {
            $this->relations[$owner] = new Relations($owner, $file);
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
    private function get(string $name): string
    {
        $file = $this->zip->getFromName($name, 0, ZipArchive::FL_UNCHANGED);
        if ($file === false) {
            throw new ResourceOpenException("Couldn't open nested file: $name");
        }

        unset($this->files[$name]);
        return $file;
    }

    /**
     * Get nested file with name
     * @param string $path
     * @return NestedFile
     * @throws ResourceOpenException
     */
    private function nested(string $path): NestedFile
    {
        return new NestedFile($path, $this->get($path), $this->getRelation($path), $this->types);
    }

    /**
     * Get all prepared files from Relations
     * @param Relations $relations
     * @return iterable
     * @throws ResourceOpenException
     */
    private function getRelationsFiles(Relations $relations): iterable
    {
        foreach ($relations->getPreparedFiles() as $filePath) {
            yield $filePath => $this->nested($filePath);
        }
    }

    /** @inheritdoc  */
    public function getPreparedFiles(): iterable
    {
        $documentPath = $this->types->getDocumentPath();

        yield $documentPath => $this->nested($documentPath);
        yield from $this->getRelationsFiles($this->getRelation($documentPath));
    }

    /** @inheritdoc  */
    public function getLeftoverFiles(): iterable
    {
        foreach ($this->files as $name => $_) {
            yield $name => $this->get($name);
        }

        foreach ($this->relations as $relations) {
            yield from $relations->getLeftoverFiles();
            yield $relations->getPath() => $relations->getContent();
        }

        $this->zip->close();
    }
}
