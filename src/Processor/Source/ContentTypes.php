<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DOMDocument;

final class ContentTypes
{
    private const DEFAULT_DOCUMENT_XML = 'word/document.xml';
    private const DOCUMENT_TYPES = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml'
    ];

    private DOMDocument $dom;
    private string $document;

    public function __construct(string $content)
    {
        $this->dom = new DOMDocument();
        $this->dom->loadXML($content);
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

    /**
     * Get main document file path
     * @return string
     */
    public function getDocumentPath(): string
    {
        if (isset($this->document)) {
            return $this->document;
        }

        /** @var \DOMElement $override */
        foreach ($this->dom->getElementsByTagName('Override') as $override) {
            $type = $override->getAttribute('ContentType');
            if (in_array($type, self::DOCUMENT_TYPES, true)) {
                return $this->document ??= trim($override->getAttribute('PartName'), '/');
            }
        }

        return $this->document ??= self::DEFAULT_DOCUMENT_XML;
    }
}
