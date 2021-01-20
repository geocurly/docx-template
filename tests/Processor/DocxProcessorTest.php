<?php

namespace DocxTemplate\Tests\Processor;

use DocxTemplate\Contract\Processor\BindFactory as Factory;
use DocxTemplate\Processor\Source\Docx;
use DocxTemplate\Processor\DocxProcessor;
use DocxTemplate\Tests\Common\BindTrait;
use DocxTemplate\Tests\Common\DocxTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\DocxProcessor
 */
class DocxProcessorTest extends TestCase
{
    use DocxTrait;
    use BindTrait;

    public function testRun(): void
    {
        $processor = new DocxProcessor($this->getDocx(), $this->getFactory());

        $files = [];
        foreach ($processor->run() as $path => $content) {
            $files[$path] = preg_replace('/>\s+/', '>', $content);
        }

        self::assertEquals(
            [
                'word/document.xml' => '<document>There is something good</document>',
                'word/header1.xml' => '<header1>There is header1</header1>',
                'word/header2.xml' => '<header2>There is header2</header2>',
                'word/footer1.xml' => '<footer1>There is footer1</footer1>',
                'word/custom1.xml' => '<custom1></custom1>',
                'word/custom2.xml' => '<custom2></custom2>',
                'word/_rels/document.xml.rels' => self::getRelationsContent(),
                'word/_rels/header1.xml.rels' => self::getEmptyRelationsContent(),
                'word/_rels/header2.xml.rels' => self::getEmptyRelationsContent(),
                'word/_rels/footer1.xml.rels' => self::getEmptyRelationsContent(),
                '[Content_Types].xml' => self::getContentTypeContent(),
            ],
            $files
        );
    }

    private function getDocx(): Docx
    {
        return self::docxMock([
            '[Content_Types].xml' => self::getContentTypeContent(),
            'word/document.xml' => '<document>${body1} something ${body2}</document>',
            'word/header1.xml' => '<header1>${header1}</header1>',
            'word/header2.xml' => '<header2>${header2}</header2>',
            'word/footer1.xml' => '<footer1>${footer1}</footer1>',
            'word/_rels/document.xml.rels' => self::getRelationsContent(),
            'word/custom1.xml' => '<custom1></custom1>',
            'word/custom2.xml' => '<custom2></custom2>',
        ]);
    }

    private function getFactory(): Factory
    {
        return self::mockBindFactory([
            'body1' => fn() => 'There is',
            'body2' => fn() => 'good',
            'header1' => fn() => 'There is header1',
            'header2' => fn() => 'There is header2',
            'footer1' => fn() => 'There is footer1',
        ]);
    }
}
