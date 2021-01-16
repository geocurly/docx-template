<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Common;

use DocxTemplate\Processor\Source\Docx;

trait DocxTrait
{
    public static function docxMock(array $files): Docx
    {
        $zip = new class($files) extends \ZipArchive {
            private array $index;
            private array $files;

            public function __construct($files)
            {
                $this->numFiles = count($files);
                $this->index = array_keys($files);
                $this->files = $files;
            }

            public function getNameIndex($index, $length = 0, $flags = null)
            {
                return $this->index[$index] ?? false;
            }

            public function getFromName($name, $length = 0, $flags = null)
            {
                return $this->files[$name] ?? false;
            }

            public function open($filename, $flags = null): bool
            {
                return true;
            }

            public function close(): bool
            {
                return true;
            }
        };

        return new Docx('', $zip);
    }

    public static function getContentTypeContent(array $types = []): string
    {
        return implode('', [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">',
            '<Default Extension="jpeg" ContentType="image/jpeg"/>',
            '<Default Extension="png" ContentType="image/png"/>',
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
            '<Default Extension="xml" ContentType="application/xml"/>',
            '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>',
            ...$types,
            '</Types>',
        ]);
    }

    public static function getRelationsContent(array $relationships = []): string
    {
        return implode('', [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">',
            '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>',
            '<Relationship Id="rId7" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>',
            '<Relationship Id="rId8" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header2.xml"/>',
            '<Relationship Id="rId9" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>',
            ...$relationships,
            '</Relationships>'
        ]);
    }

    public static function getEmptyRelationsContent(): string
    {
        return implode('', [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>',
        ]);
    }
}
