<?php

namespace DocxTemplate\Tests\Processor\Source;

use DocxTemplate\Processor\Source\Relation;
use DocxTemplate\Processor\Source\Relations;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Source\Relations
 */
class RelationsTest extends TestCase
{
    private Relations $relations;

    private const DEFAULT_XML = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>' .
        '<Relationship Id="rId7" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>' .
        '<Relationship Id="rId8" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header2.xml"/>' .
        '<Relationship Id="rId9" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>' .
        '</Relationships>'
        ];

    protected function setUp(): void
    {
        $this->relations = new Relations(
            'name',
            implode("\r\n", self::DEFAULT_XML)
        );
    }

    public function testGetXml(): void
    {
        $this->relations->add(
            new Relation(
                'image.png',
                'rId1000',
                'media/image1000.png',
                'test-type'
            )
        );

        $expect = [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>' .
            '<Relationship Id="rId7" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>' .
            '<Relationship Id="rId8" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header2.xml"/>' .
            '<Relationship Id="rId9" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>' .
            '<Relationship Id="rId1000" Type="test-type" Target="media/image1000.png"/>' .
            '</Relationships>'
        ];

        self::assertEquals(
            preg_replace('/\s+/', '', implode("\r\n", $expect)),
            preg_replace('/\s+/', '', $this->relations->getXml())
        );
    }

    public function testGetNextId(): void
    {
        self::assertEquals(
            $this->relations->getNextId(),
            'rId6'
        );
    }

    public function testGetFiles(): void
    {
        $files = [];
        foreach ($this->relations->getFiles() as $file) {
            $files[] = $file;
        }

        self::assertEquals(
            [
                'word/header1.xml',
                'word/header2.xml',
                'word/footer1.xml',
            ],
            $files
        );
    }

    public function testGetName(): void
    {
        self::assertEquals('name', $this->relations->getName());
    }
}
