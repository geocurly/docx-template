<?php

namespace DocxTemplate\Tests\Processor\Process;

use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Processor\Process\ProcessFactory;
use DocxTemplate\Processor\Process\SimpleContentProcess;
use DocxTemplate\Processor\Process\TableContentProcess;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Process\ProcessFactory
 */
class ProcessFactoryTest extends TestCase
{

    public function testMake(): void
    {
        $xml = <<<XML
            <document>
                <w:p><w:t>There is something</w:t></w:p>
                <w:tbl>
                    <w:tr><w:tc><w:p><w:r><w:t>1</w:t></w:r></w:p></w:tc></w:tr>
                    <w:tr>
                        <w:tc>
                            <w:tcPr>
                                <w:vMerge w:val="restart"/>  
                            </w:tcPr>
                            <w:p><w:r><w:t>1</w:t></w:r></w:p>
                        </w:tc>
                    </w:tr>
                    <w:tr>
                        <w:tc>
                            <w:tcPr>
                                <w:vMerge w:val="continue"/>  
                            </w:tcPr>
                            <w:p><w:r><w:t>1</w:t></w:r></w:p>
                        </w:tc>
                    </w:tr>
                </w:tbl>
                <w:tbl>
                    <w:tr>
                        <w:tc>
                            <w:tcPr>
                                <w:vMerge w:val="restart"/>  
                            </w:tcPr>
                            <w:p><w:r><w:t>1</w:t></w:r></w:p>
                        </w:tc>
                    </w:tr>
                    <w:tr><w:tc><w:p><w:r><w:t>1</w:t></w:r></w:p></w:tc></w:tr>
                </w:tbl>
                <w:tbl><w:tr><w:tc><w:p><w:r><w:t>2</w:t></w:r></w:p></w:tc></w:tr></w:tbl>
                <w:p><w:r><w:t>2</w:t></w:r></w:p>
                <w:tbl><w:tr><w:tc><w:p><w:r><w:t>3</w:t></w:r></w:p></w:tc></w:tr></w:tbl>
            </document>
            XML;

        $xml = preg_replace('/>\s+</', '><', $xml);
        $file = $this->getMockBuilder(RelationContainer::class)->getMock();
        $file->method('getContent')->willReturn($xml);

        $factory = new ProcessFactory();
        $processes = [];
        foreach ($factory->make($file) as $process) {
            $processes[] = $process;
        }

        $tbl1 = <<<'XML'
            <w:tbl>
                <w:tr><w:tc><w:p><w:r><w:t>1</w:t></w:r></w:p></w:tc></w:tr>
                <w:tr>
                    <w:tc>
                        <w:tcPr>
                            <w:vMerge w:val="restart"/>  
                        </w:tcPr>
                        <w:p><w:r><w:t>1</w:t></w:r></w:p>
                    </w:tc>
                </w:tr>
                <w:tr>
                    <w:tc>
                        <w:tcPr>
                            <w:vMerge w:val="continue"/>  
                        </w:tcPr>
                        <w:p><w:r><w:t>1</w:t></w:r></w:p>
                    </w:tc>
                </w:tr>
            </w:tbl>
            XML;

        $tbl2 = <<<'XML'
            <w:tbl>
                <w:tr>
                    <w:tc>
                        <w:tcPr>
                            <w:vMerge w:val="restart"/>  
                        </w:tcPr>
                        <w:p><w:r><w:t>1</w:t></w:r></w:p>
                    </w:tc>
                </w:tr>
                <w:tr><w:tc><w:p><w:r><w:t>1</w:t></w:r></w:p></w:tc></w:tr>
            </w:tbl>
            XML;
        self::assertEquals(
            [
                new SimpleContentProcess('<document><w:p><w:t>There is something</w:t></w:p>', $file),
                new TableContentProcess(preg_replace('/>\s+</', '><', $tbl1), $file),
                new TableContentProcess(preg_replace('/>\s+</', '><', $tbl2), $file),
                new TableContentProcess(
                    '<w:tbl><w:tr><w:tc><w:p><w:r><w:t>2</w:t></w:r></w:p></w:tc></w:tr></w:tbl>',
                    $file
                ),
                new SimpleContentProcess('<w:p><w:r><w:t>2</w:t></w:r></w:p>', $file),
                new TableContentProcess(
                    '<w:tbl><w:tr><w:tc><w:p><w:r><w:t>3</w:t></w:r></w:p></w:tc></w:tr></w:tbl>',
                    $file
                ),
                new SimpleContentProcess('</document>', $file),
            ],
            $processes
        );
    }
}
