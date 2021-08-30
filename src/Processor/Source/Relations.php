<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DocxTemplate\Contract\Processor\Source\Relation;
use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Contract\Processor\Source\Source;
use DOMDocument;

final class Relations implements Source, RelationContainer
{
    private DOMDocument $dom;
    private array $files;
    private array $ids = [];
    private int $count;
    /** @var Image[] */
    private array $unprocessed = [];


    /**
     * Resources constructor.
     * @param string $owner
     * @param string $path
     * @param string|null $content
     */
    public function __construct(
        private string $owner,
        private string $path,
        string $content = null
    ) {
        $content ??= <<<XML
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>
        XML;

        $this->dom = new DOMDocument();
        $this->dom->loadXML($content);

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

    /** @inheritdoc  */
    public function getPreparedFiles(): iterable
    {
        return $this->files;
    }


    /** @inheritdoc  */
    public function getLeftoverFiles(): iterable
    {
        foreach ($this->unprocessed as $relation) {
            yield trim($relation->getTarget(), '/') => $relation->getContent();
        }
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
     * Get relation content
     * @return string
     */
    public function getContent(): string
    {
        return $this->dom->saveXML();
    }

    /** @inheritdoc  */
    public function getNextRelationId(): string
    {
        do {
            $this->count++;
            $id = "rId{$this->count}";
        } while (in_array($id, $this->ids, true));

        return $id;
    }

    /**
     * Add given relation to collection
     * @param Relation $relation
     */
    public function add(Relation $relation): void
    {
        $newRelation = $this->dom->createElement('Relationship');
        $newRelation->setAttribute('Id', $relation->getId());
        $newRelation->setAttribute('Type', $relation->getType());
        $newRelation->setAttribute('Target', $relation->getTarget());

        $this->dom->getElementsByTagName('Relationships')
            ->item(0)
            ->appendChild($newRelation);

        $this->unprocessed[] = $relation;
    }
}
