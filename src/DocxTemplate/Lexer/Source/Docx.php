<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Source;

use DocxTemplate\Lexer\Contract\SourceInterface;
use DocxTemplate\Lexer\Exception\DocxException;
use DOMDocument;
use \ZipArchive as Zip;
use function GuzzleHttp\Psr7\stream_for as makeStreamFromString;

class Docx implements SourceInterface
{
    private const CONTENT_TYPE_XML = '[Content_Types].xml';
    private const DEFAULT_DOCUMENT_XML = 'word/document.xml';

    /**
     * Content map
     * @var string[] = [
     *     "$filename" => "$filenameContent"
     * ]
     */
    private array $source = [];

    /**
     * Relations for files
     * @var array = [
     *     "$filename" => "$relationsContent"
     * ]
     */
    private array $relations = [];

    private string $filepath;
    private Zip $zip;

    /** @var string $contentFilename docx main part filename */
    private string $contentFilename;

    public function __construct(string $fromFile, string $toFile)
    {
        if (!@copy($fromFile, $toFile)) {
            throw new DocxException("Could not copy $fromFile to $toFile");
        }

        $this->zip = new Zip();
        $isZipOpened = $this->zip->open($toFile, Zip::CHECKCONS) === true;
        if (!$isZipOpened) {
            throw new DocxException("Could not open zip file: $toFile");
        }

        $this->filepath = $toFile;

        $this->source[$this->getContentFilename()] = $this->getSource($this->getContentFilename());
        foreach ($this->getIndexFilenames() as $filename) {
            $this->source[$filename] = $this->getSource($filename);
            $relations = $this->getRelations($filename);
            if ($relations !== null) {
                $this->relations[$filename] = $relations;
            }
        }
    }

    /**
     * Get zip filenames for index
     * @return iterable
     */
    private function getIndexFilenames(): iterable
    {
        yield self::CONTENT_TYPE_XML;

        $DOMRelations = new DOMDocument();
        $DOMRelations->loadXML(
            $this->zip->getFromName($this->getRelationsName($this->getContentFilename()))
        );

        /** @var \DOMNode $relations */
        $relations = $DOMRelations->getElementsByTagName('Relationships')[0];
        $relations = $relations === null ? [] : $relations->childNodes;
        /** @var \DOMElement $relation */
        foreach ($relations as $relation) {
            if (!$relation->hasAttributes()) {
                continue;
            }

            $type = substr($relation->getAttribute('Type'), -20);
            if (in_array($type, ['relationships/footer', 'relationships/header'])) {
                yield "word/{$relation->getAttribute('Target')}";
            }
        }
    }

    /**
     * Get relations content for file
     * @param string $filename
     * @return string|null
     */
    final protected function getRelations(string $filename): ?string
    {
        if (isset($this->relations[$filename])) {
            return $this->relations[$filename];
        }

        $content = $this->zip->getFromName($this->getRelationsName($filename));
        return $content === false ? null : $content;
    }

    /**
     * Get file content
     * @param string $filename
     * @return string|null
     */
    final protected function getSource(string $filename): ?string
    {
        if (isset($this->source[$filename])) {
            return $this->source[$filename];
        }

        $content = $this->zip->getFromName($filename);
        return $content === false ? null : $content;
    }

    final private function getRelationsName(string $filename): string
    {
        return 'word/_rels/' . basename($filename) . '.rels';
    }

    /**
     * Получаем имя главной части документа
     * @return string
     */
    final protected function getContentFilename(): string
    {
        if (isset($this->contentFilename)) {
            return $this->contentFilename;
        }

        $pattern = '~PartName="\/(word\/document.*?\.xml)" ' .
            'ContentType="application\/vnd\.openxmlformats-officedocument\.wordprocessingml\.document\.main\+xml"~';
        if (preg_match($pattern, $this->getSource(self::CONTENT_TYPE_XML), $matches) === 1) {
            return $this->contentFilename ??= $matches[1];
        }

        return $this->contentFilename ??= self::DEFAULT_DOCUMENT_XML;
    }

    /** @inheritDoc */
    public function getStreams(): iterable
    {
        foreach ($this->source as $filename => $content) {
            yield $filename => makeStreamFromString($content);
        }
    }
}