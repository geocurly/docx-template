<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Processor\Source\Docx;
use DOMDocument;
use Psr\Http\Message\StreamInterface;

class TemplateProcessor
{
    private Docx $docx;

    /**
     * TemplateProcessor constructor.
     * @param string $source
     * @throws Exception\ResourceOpenException
     */
    public function __construct(string $source)
    {
        $this->docx = new Docx($source);
    }

    /**
     * Run template processing
     *
     * @return iterable
     * @throws Exception\TemplateException
     */
    public function run(): iterable
    {
        foreach ($this->docx->getRelations() as $main => $relations) {
            $DOMRelations = $this->relations($relations);
            yield $main => $this->process($main, $DOMRelations);

            foreach ($this->getRelationFiles($DOMRelations) as $file) {
                yield $file => $this->process($file, $DOMRelations);
            }

            yield $relations => $DOMRelations->saveXML();
        }

        yield from $this->docx->flush();
    }

    /**
     * Start template processing
     *
     * @param string $name file to process
     * @param DOMDocument $DOMRelations
     * @return string|StreamInterface
     * @throws Exception\ResourceOpenException
     */
    private function process(string $name, DOMDocument $DOMRelations) /*: string|StreamInterface */
    {
        return $this->docx->get($name);
    }

    /**
     * Get DOMDocument with docx relations
     * @param string $relation
     * @return DOMDocument
     * @throws Exception\ResourceOpenException
     */
    private function relations(string $relation): DOMDocument
    {
        $relations = new DOMDocument();
        $relations->loadXML($this->docx->get($relation));
        return $relations;
    }

    /**
     * Get relation files to template processing
     * @param DOMDocument $DOMRelations
     * @return iterable
     */
    private function getRelationFiles(DOMDocument $DOMRelations): iterable
    {
        $relations = $DOMRelations->getElementsByTagName('Relationships')[0];
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
}
