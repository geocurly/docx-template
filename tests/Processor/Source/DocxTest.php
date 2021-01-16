<?php

namespace DocxTemplate\Tests\Processor\Source;

use DocxTemplate\Contract\Processor\Source\Source;
use DocxTemplate\Processor\Source\Docx;
use DocxTemplate\Tests\Common\DocxTrait;

use PHPUnit\Framework\TestCase;

/** @covers \DocxTemplate\Processor\Source\Docx */
class DocxTest extends TestCase
{
    use DocxTrait;

    private Docx $docx;

    protected function setUp(): void
    {
        $this->docx = self::docxMock([
            '[Content_Types].xml' => self::getContentTypeContent(),
            'word/document.xml' => '<document>${body}</document>',
            'word/header1.xml' => '<header1>${header1}</header1>',
            'word/header2.xml' => '<header2>${header2}</header2>',
            'word/footer1.xml' => '<footer1>${footer1}</footer1>',
            'word/_rels/document.xml.rels' => self::getRelationsContent(),
            'word/custom1.xml' => '<custom1></custom1>',
            'word/custom2.xml' => '<custom2></custom2>',
        ]);
    }

    public function testGetPreparedFiles(): void
    {
        $prepared = [];
        foreach ($this->docx->getPreparedFiles() as $path => $file) {
            $prepared[] = $path;
        }

        $leftover = [];
        foreach ($this->docx->getLeftoverFiles() as $path => $_) {
            $leftover[] = $path;
        }

        self::assertEquals(
            [
                [
                    'word/document.xml',
                    'word/header1.xml',
                    'word/header2.xml',
                    'word/footer1.xml',
                ],
                [
                    'word/custom1.xml',
                    'word/custom2.xml',
                    'word/_rels/document.xml.rels',
                    'word/_rels/header1.xml.rels',
                    'word/_rels/header2.xml.rels',
                    'word/_rels/footer1.xml.rels',
                    '[Content_Types].xml',
                ]
            ],
            [
                $prepared,
                $leftover,
            ]
        );
    }
}
