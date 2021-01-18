<?php

namespace DocxTemplate\Tests\Processor\Process;

use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Processor\Process\ProcessFactory;
use DocxTemplate\Processor\Process\SimpleContentProcess;
use DocxTemplate\Processor\Process\TableRowContentProcess;
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
                <w:p>
                   <w:t>There is something</w:t> 
                </w:p>
                <w:tbl>
                    <w:tr>
                        <w:tc>
                          <w:p>
                            <w:r>
                              <w:t>1</w:t>
                            </w:r>
                          </w:p>
                        </w:tc>
                    </w:tr>
                </w:tbl>
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

        self::assertEquals(
            [
                new SimpleContentProcess(
                    '<document><w:p><w:t>There is something</w:t></w:p><w:tbl>',
                    $file
                ),
                new TableRowContentProcess(
                    '<w:tr><w:tc><w:p><w:r><w:t>1</w:t></w:r></w:p></w:tc></w:tr>',
                    $file,
                ),
                new SimpleContentProcess('</w:tbl></document>', $file),
            ],
            $processes
        );
    }
}
