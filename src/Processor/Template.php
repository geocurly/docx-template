<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Exception\Processor\ResourceOpenException;
use Psr\Http\Message\StreamInterface;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

class Template
{
    private TemplateProcessor $processor;

    /**
     * Docx constructor.
     * @param TemplateProcessor $processor
     */
    public function __construct(TemplateProcessor $processor)
    {
        $this->processor = $processor;
    }

    public function stream(string $uri): void
    {
        $output = fopen($uri, 'wr+');
        if ($output === false) {
            throw new ResourceOpenException("Couldn't open resource: $uri");
        }

        $options = new Archive();
        $options->setOutputStream($output);

        $out = new ZipStream(null, $options);
        foreach ($this->processor->run() as $name => $fileContent) {
            if ($fileContent instanceof StreamInterface) {
                $out->addFileFromPsr7Stream($name, $fileContent);
                continue;
            }

            if (is_resource($fileContent)) {
                $out->addFileFromStream($name, $fileContent);
                continue;
            }

            $out->addFile($name, $fileContent);
        }

        $out->finish();
        fclose($output);
    }
}
