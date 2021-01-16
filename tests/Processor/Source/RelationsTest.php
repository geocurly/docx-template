<?php

namespace DocxTemplate\Tests\Processor\Source;

use DocxTemplate\Processor\Source\Image;
use DocxTemplate\Processor\Source\Relations;
use DocxTemplate\Tests\Common\DocxTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Source\Relations
 */
class RelationsTest extends TestCase
{
    use DocxTrait;

    private Relations $relations;

    protected function setUp(): void
    {
        $this->relations = new Relations(
            'word/name.xml',
            'word/rels/name.xml.rels',
            self::getRelationsContent()
        );
    }

    public function testGetXml(): void
    {
        $this->relations->add(new Image(
            'rId1000',
            realpath(__DIR__ . '/../../Fixture/Image/cat.jpeg'),
        ));

        $expect = self::getRelationsContent([
            <<<'XML'
            <Relationship Id="rId1000" 
                          Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" 
                          Target="/word/media/rId1000.jpeg"/>
            XML
        ]);

        self::assertEquals(
            preg_replace('/\s+/', '', $expect),
            preg_replace('/\s+/', '', $this->relations->getContent())
        );
    }

    public function testGetNextId(): void
    {
        self::assertEquals(
            $this->relations->getNextRelationId(),
            'rId6'
        );
    }

    public function testGetLeftoverFiles(): void
    {
        $this->relations->add(new Image('rId', __DIR__ . '/../../Fixture/Image/cat.jpeg'));
        $files = [];
        foreach ($this->relations->getLeftoverFiles() as $path => $_) {
            $files[] = $path;
        }

        self::assertEquals(
            ['word/media/rId.jpeg'],
            $files,
        );
    }

    public function testGetFiles(): void
    {
        $files = [];
        foreach ($this->relations->getPreparedFiles() as $file) {
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

    public function testGetPath(): void
    {
        self::assertEquals(
            'word/rels/name.xml.rels',
            $this->relations->getPath()
        );
    }

    public function testGetOwnerPath(): void
    {
        self::assertEquals(
            'word/name.xml',
            $this->relations->getOwnerPath()
        );
    }
}
