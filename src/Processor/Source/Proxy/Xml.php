<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source\Proxy;

use DocxTemplate\Processor\Exception\ResourceOpenException;
use XMLReader;
use XMLWriter;

class Xml
{
    private XMLReader $reader;

    /**
     * Xml constructor.
     * @param string $source
     * @throws ResourceOpenException
     */
    public function __construct(string $source)
    {
        $this->reader = new XMLReader();
        if (!$this->reader->open($source)) {
            throw new ResourceOpenException("Couldn't open xml resource: {$source}");
        }
    }

    /**
     * @param string $output
     * @param array|null $filters
     * @return iterable
     * @throws ResourceOpenException
     */
    public function write(string $output, array $filters = null): iterable
    {
        $writer = new XMLWriter();
        if (!$writer->openUri($output)) {
            throw new ResourceOpenException("Couldn't open output resource: {$output}");
        }

        $writer->startDocument('1.0', 'utf-8', 'yes');
        while ($this->reader->read()) {
            if ($filters !== null && $this->isIntercept($this->reader, $filters)) {
                yield $this->reader->readOuterXml();
            }

            $this->accept($this->reader, $writer);
        }
    }

    /**
     * Check if need intercept current node
     *
     * @param XMLReader $reader
     * @param array|null $filters
     * @return bool
     */
    protected function isIntercept(XMLReader $reader, array $filters): bool
    {
        if (!in_array($reader->nodeType, array_keys($filters), true)) {
            return false;
        }

        foreach ($filters as $node => $names) {
            if (in_array($reader->name, $names, true)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Accept current node
     * @param XMLReader $reader
     * @param XMLWriter $writer
     */
    private function accept(XMLReader $reader, XMLWriter $writer): void
    {
        switch ($reader->nodeType) {
            case XMLReader::ELEMENT:
                $writer->startElement($reader->name);

                if ($reader->moveToFirstAttribute()) {
                    do {
                        $writer->writeAttribute($reader->name, $reader->value);
                    } while ($reader->moveToNextAttribute());
                    $reader->moveToElement();
                }

                if ($reader->isEmptyElement) {
                    $writer->endElement();
                }
                break;

            case XMLReader::END_ELEMENT:
                $writer->endElement();
                break;

            case XMLReader::COMMENT:
                $writer->writeComment($reader->value);
                break;

            case XMLReader::CDATA:
                $writer->writeCdata($reader->value);
                break;

            case XMLReader::SIGNIFICANT_WHITESPACE:
            case XMLReader::WHITESPACE:
            case XMLReader::TEXT:
                $writer->text($reader->value);
                break;

            case XMLReader::PI:
                $writer->writePi($reader->name, $reader->value);
                break;
        }
    }
}