<?php

namespace DocxTemplate\Tests\Processor\Process;

use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Processor\Process\SimpleContentProcess;
use DocxTemplate\Tests\Common\BindTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Process\SimpleContentProcess
 */
class SimpleContentProcessTest extends TestCase
{
    use BindTrait;

    public function testRun(): void
    {
        $factory = self::mockBindFactory([
            'var' => fn() => 'var value',
            'foo' => fn() => 'foo value',
            'bar' => fn() => 'bar value',
        ]);

        $process = new SimpleContentProcess(
            '${ var `is not ${ foo } and not ${ bar }`}',
            $this->getContainer()
        );

        self::assertSame(
            'var value is not foo value and not bar value',
            $process->run($factory)
        );
    }

    private function getContainer(): RelationContainer
    {
        return $this->getMockBuilder(RelationContainer::class)->getMock();
    }
}
