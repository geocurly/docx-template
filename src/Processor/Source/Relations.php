<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DOMDocument;

final class Relations
{
    private DOMDocument $dom;
    private string $name;
    private array $files;
    private array $ids;
    private int $count;

    /**
     * Resources constructor.
     * @param string $name
     * @param string $content
     */
    public function __construct(string $name, string $content)
    {
        $this->dom = new DOMDocument();
        $this->dom->loadXML($content);
        $this->name = $name;

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
    public function getName(): string
    {
        return $this->name;
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
     * @param Relation $relation
     * @return $this
     */
    public function add(Relation $relation): self
    {
        $newRelation = $this->dom->createElement('Relationship');
        $newRelation->setAttribute('Id', $relation->getId());
        $newRelation->setAttribute('Type', $relation->getType());
        $newRelation->setAttribute('Target', $relation->getTarget());

        $this->dom->getElementsByTagName('Relationships')
            ->item(0)
            ->appendChild($newRelation);

        return $this;
    }
}
