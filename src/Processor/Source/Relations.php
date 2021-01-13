<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DOMDocument;

final class Relations
{
    private DOMDocument $dom;
    private string $owner;
    private array $files;
    private array $ids = [];
    private int $count;
    private string $path;
    /** @var Image[] */
    private array $unprocessed = [];


    /**
     * Resources constructor.
     * @param string $owner
     * @param string $path
     * @param string|null $content
     */
    public function __construct(string $owner, string $path,  string $content = null)
    {
        $content ??= <<<XML
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>
        XML;

        $this->dom = new DOMDocument();
        $this->dom->loadXML($content);
        $this->owner = $owner;
        $this->path = $path;

        $this->init();
    }

    private function init(): void
    {
        $this->files = [];
        $this->count = 0;
        $relations = $this->dom->getElementsByTagName('Relationships')[0];
        $relations = $relations === null ? [] : $relations->childNodes;
        foreach ($relations as $relation) {
            if (!$relation->hasAttributes()) {
                continue;
            }

            /** try to find footer or header */
            $type = substr($relation->getAttribute('Type'), -20);
            if (in_array($type, ['relationships/footer', 'relationships/header'])) {
                $this->files[] = "word/{$relation->getAttribute('Target')}";
            }

            $this->ids[] = $relation->getAttribute('Id');
            $this->count++;
        }
    }

    /**
     * Get relation files
     * @return iterable
     */
    public function getFiles(): iterable
    {
        return $this->files;
    }

    /**
     * Get relations file name
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get relation owner path
     * @return string
     */
    public function getOwnerPath(): string
    {
        return $this->owner;
    }

    /**
     * Get relation xml
     * @return string
     */
    public function getXml(): string
    {
        return $this->dom->saveXML();
    }

    /**
     * Get next relation id
     * @return string
     */
    public function getNextId(): string
    {
        do {
            $this->count++;
            $id = "rId{$this->count}";
        } while (in_array($id, $this->ids, true));

        return $id;
    }

    /**
     * Add given relation to collection
     * @param Image $image
     * @return $this
     */
    public function add(Image $image): self
    {
        $newRelation = $this->dom->createElement('Relationship');
        $newRelation->setAttribute('Id', $image->getId());
        $newRelation->setAttribute('Type', $image->getType());
        $newRelation->setAttribute('Target', $image->getTarget());

        $this->dom->getElementsByTagName('Relationships')
            ->item(0)
            ->appendChild($newRelation);

        $this->unprocessed[] = $image;
        return $this;
    }

    /**
     * @return Image[]
     */
    public function getUnprocessed(): array
    {
        return $this->unprocessed;
    }
}
