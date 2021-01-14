<?php

namespace DocxTemplate\Tests\Ast;

use DocxTemplate\Ast\NodePosition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Ast\NodePosition
 *
 * Class NodePositionTest
 * @package DocxTemplate\Tests\Ast
 */
class NodePositionTest extends TestCase
{

    public function testGetEnd(): void
    {
        $pos = $this->pos(5, 10);
        self::assertEquals( 15, $pos->getEnd());

        $pos = $this->pos(0, 0);
        self::assertEquals( 0, $pos->getEnd());
    }

    public function testGetLength(): void
    {
        $pos = $this->pos(2, 10);
        self::assertEquals(10, $pos->getLength());
    }

    public function testToArray(): void
    {
        $pos = $this->pos(2, 10);
        self::assertEquals(
            [
                'start' => 2,
                'end' => 12,
            ],
            $pos->toArray()
        );
    }

    public function testGetStart(): void
    {
        $pos = $this->pos(2, 10);
        self::assertEquals(2, $pos->getStart());
    }

    public function testChange(): void
    {
        $pos = $this->pos(2, 10);
        $pos->change(5, 20);
        self::assertEquals([5, 25], [$pos->getStart(), $pos->getEnd()]);
    }

    public function testChangeNext(): void {

        $pos = $this->pos(1, 10);
        $next = $this->pos(11, 2);
        if ($next !== null) {
            $pos->addNext($next);
        }

        $pos->change(2, 25);

        self::assertEquals(
            [27, 29],
            [$next->getStart(), $next->getEnd()]
        );
    }

    private function pos(int $start, int $len): NodePosition
    {
        return new NodePosition($start, $len);
    }
}
