<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DOMDocument;

class Relations
{
    private DOMDocument $dom;
    private string $name;

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
    }

    /**
     * Get relation files
     * @return iterable
     */
    public function getFiles(): iterable
    {
        $relations = $this->dom->getElementsByTagName('Relationships')[0];
        $relations = $relations === null ? [] : $relations->childNodes;
        foreach ($relations as $relation) {
            if (!$relation->hasAttributes()) {
                continue;
            }

            /** try to find footer or header */
            $type = substr($relation->getAttribute('Type'), -20);
            if (in_array($type, ['relationships/footer', 'relationships/header'])) {
                yield "word/{$relation->getAttribute('Target')}";
            }
        }
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
}
