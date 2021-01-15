<?php

namespace DocxTemplate\Tests\Processor\Source;

use DocxTemplate\Processor\Source\ContentTypes;
use DocxTemplate\Tests\Common\DocxTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Source\ContentTypes
 */
class ContentTypesTest extends TestCase
{
    use DocxTrait;

    private ContentTypes $types;

    protected function setUp(): void
    {
        $this->types = new ContentTypes( self::getContentTypeContent());
    }

    public function testGetXml(): void
    {
        $this->types->add('/word/media/image.jpeg', 'image/jpeg');

        $xml = self::getContentTypeContent(['<Override PartName="/word/media/image.jpeg" ContentType="image/jpeg"/>']);
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
