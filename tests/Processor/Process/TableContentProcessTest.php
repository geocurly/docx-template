<?php

namespace DocxTemplate\Tests\Processor\Process;

use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Processor\Process\TableContentProcess;
use DocxTemplate\Tests\Common\BindTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Process\TableContentProcess
 */
class TableContentProcessTest extends TestCase
{
    use BindTrait;

    /**
     * @dataProvider runProvider
     *
     * @param array $factory
     * @param string $content
     * @param string $expected
     */
    public function testRun(array $factory, string $content, string $expected): void
    {
        $factory = self::mockBindFactory(...$factory);
        $process = new TableContentProcess($content, $this->getContainer());

        self::assertSame($expected, $process->run($factory));
    }

    public function runProvider(): array
    {
        return [
            [
                [['var' => fn() => 'var value', 'foo' => fn() => 'foo value', 'bar' => fn() => 'bar value']],
                '${ var `is not ${ foo } and not ${ bar }`}',
                'var value is not foo value and not bar value',
            ],
            [
                [['index' => fn() => 'There is index']],
                '<w:tr><w:tc><w:p><w:r><w:t>${index}</w:t></w:r></w:p></w:tc></w:tr>',
                '<w:tr><w:tc><w:p><w:r><w:t>There is index</w:t></w:r></w:p></w:tc></w:tr>',
            ],
//            [
//                [[], [], [], ['index' => [['index' => 'There is index']]]],
//                '<w:tr><w:tc><w:p><w:r><w:t>${index}</w:t></w:r></w:p></w:tc></w:tr>',
//                '<w:tr><w:tc><w:p><w:r><w:t>There is index</w:t></w:r></w:p></w:tc></w:tr>',
//            ],
// TODO there is next iteration
//            [
//                [
//                    [],
//                    [],
//                    [],
//                    [
//                        'index' => [
//                            ['index' => 'There is index1', 'foo' => 'There is foo1'],
//                            ['index' => 'There is index2', 'foo' => 'There is foo2'],
//                        ],
//                    ],
//                ],
//                <<<'XML'
//                <w:tr><w:tc><w:p><w:r><w:t>${index}, {$foo}</w:t></w:r></w:p></w:tc></w:tr>
//                XML,
//                <<<'XML'
//                <w:tr><w:tc><w:p><w:r><w:t>There is index1, There is foo1</w:t></w:r></w:p></w:tc></w:tr>
//                <w:tr><w:tc><w:p><w:r><w:t>There is index2, There is foo2</w:t></w:r></w:p></w:tc></w:tr>
//                XML,
//            ]
        ];
    }

    private function getContainer(): RelationContainer
    {
        return $this->getMockBuilder(RelationContainer::class)->getMock();
    }
}
