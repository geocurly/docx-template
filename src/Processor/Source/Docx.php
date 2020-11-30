<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;

use DocxTemplate\Processor\Exception\ResourceOpenException;
use ZipArchive as Zip;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

class Docx
{
    private string $source;
    private Zip $zip;

    /**
     * Docx constructor.
     * @param string $source
     * @throws ResourceOpenException
     */
    public function __construct(string $source)
    {
        $this->source = $source;
        $this->zip = new Zip();
        if ($this->zip->open($source, Zip::CHECKCONS) === false) {
            throw new ResourceOpenException("Couldn't open docx document");
        }
    }

    public function stream(string $uri): void
    {
        try {
            $output = fopen($uri, 'wr+');

            $options = new Archive();
            $options->setOutputStream($output);
            $options->setDeflateLevel(-1);

            $out = new ZipStream('text.docx', $options);
            for( $i = 0; $i < $this->zip->numFiles; $i++ ) {
                $name = $this->zip->getNameIndex($i, Zip::FL_UNCHANGED);
                $out->addFile(
                    $name,
                    // TODO there is will be interception for template processing
                    $this->zip->getFromName($name, 0, Zip::FL_UNCHANGED)
                );
            }

            $out->finish();
        } finally {
            fclose($output);
        }
    }
}
