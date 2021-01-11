<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DOMDocument;

final class ContentTypes
{
    private DOMDocument $dom;
    private string $name;

    public function __construct(string $name, string $content)
    {
        $this->dom = new DOMDocument();
        $this->dom->loadXML($content);
        $this->name = $name;
    }

    /**
     * Get file name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get xml
     * @return string
     */
    public function getXml(): string
    {
        return $this->dom->saveXML();
    }

    /**
     * Add new content type
     *
     * @param string $part name of new part
     * @param string $type mime type
     * @return $this
     */
    public function add(string $part, string $type): self
    {
        $newRelation = $this->dom->createElement('Override');
        $newRelation->setAttribute('PartName', $part);
        $newRelation->setAttribute('ContentType', $type);

        $this->dom->getElementsByTagName('Types')
            ->item(0)
            ->appendChild($newRelation);

        return $this;
    }
}
