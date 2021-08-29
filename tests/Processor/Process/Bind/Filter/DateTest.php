<?php

namespace DocxTemplate\Tests\Processor\Process\Bind\Filter;

use DocxTemplate\Exception\Processor\BindException;
use DocxTemplate\Processor\Process\Bind\Filter\DateFilter;
use PHPUnit\Framework\TestCase;

/** @covers \DocxTemplate\Processor\Process\Bind\Filter\DateFilter */
class DateTest extends TestCase
{
    public function testFilterPositive()
    {
        $filter = new DateFilter('date');
        $filter->setParams('d.m.Y');
        self::assertEquals(
            '01.01.2000',
            $filter->filter('2000-01-01 00:00:00')
        );
    }

    /** @dataProvider filterNegativeProvider
     * @param array $params
     * @param string $target
     * @throws BindException
     */
    public function testFilterNegative(array $params, string $target): void
    {
        self::expectException(BindException::class);

        $filter = new DateFilter('date');
        $filter->setParams(...$params);
        $filter->filter($target);
    }

    public function filterNegativeProvider(): array
    {
        return [
            [[], '2000-01-01 00:00:00'],
            [['d.m.Y', 'second'], '2000-01-01 00:00:00'],
            [['d.m.Y'], 'not date']
        ];
    }
}
