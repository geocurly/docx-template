<?php

namespace DocxTemplate\Tests\Processor\Source;

use DocxTemplate\Processor\Source\ContentTypes;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Source\ContentTypes
 */
class ContentTypesTest extends TestCase
{
    private ContentTypes $types;

    private const DEFAULT_XML = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">',
        '<Default Extension="jpeg" ContentType="image/jpeg"/>',
        '<Default Extension="png" ContentType="image/png"/>',
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
        '<Default Extension="xml" ContentType="application/xml"/>',
        '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>',
        '</Types>',
    ];

    protected function setUp(): void
    {
        $this->types = new ContentTypes(implode("", self::DEFAULT_XML));
    }

    public function testGetXml(): void
    {
        $this->types->add('/word/media/image.jpeg', 'image/jpeg');
        $xml = implode("", [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">',
            '<Default Extension="jpeg" ContentType="image/jpeg"/>',
            '<Default Extension="png" ContentType="image/png"/>',
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
            '<Default Extension="xml" ContentType="application/xml"/>',
            '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>',
            '<Override PartName="/word/media/image.jpeg" ContentType="image/jpeg"/>',
            '</Types>',
        ]);

        self::assertEquals(
            preg_replace('/\s+/', '', $xml),
            preg_replace('/\s+/', '', $this->types->getXml())
        );
    }

    public function testGetDocumentPathFromXml(): void
    {
        self::assertEquals(
            $this->types->getDocumentPath(),
            'word/document.xml'
        );
    }

    public function testGetDocumentPathDefault(): void
    {
        $types = new ContentTypes(
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>'
        );

        self::assertEquals(
            'word/document.xml',
            $types->getDocumentPath(),
        );
    }
}
