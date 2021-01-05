<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Processor\Process;

use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Processor\BindStore;
use DocxTemplate\Processor\Process\Resolver;
use DocxTemplate\Tests\Common\BindTrait;
use DocxTemplate\Tests\Common\NodeTrait;
use PHPUnit\Framework\TestCase;

class ResolverTest extends TestCase
{
    use NodeTrait;
    use BindTrait;

    private const TEST_VALUE_1 = 'value_1';

    /**
     * @dataProvider solveProvider
     * @covers \DocxTemplate\Processor\Process\Resolver::solve
     *
     * @param Node $node
     * @param string $expected
     * @throws TemplateException
     */
    public function testSolve(Node $node, string $expected): void
    {
        $resolver = new Resolver($this->getBindStore());
        self::assertEquals(
            $expected,
            $resolver->solve($node),
            "Try to solve " . get_class($node) . " with value: $expected."
        );
    }

    public function solveProvider(): array
    {
        return [
            [
                self::block(
                    0,
                    8,
                    '${ var }',
                    self::id('var', 3, 3)
                ),
                self::TEST_VALUE_1
            ]
        ];
    }

    private function getBindStore(): BindStore
    {
        return new BindStore([
            self::valuableBind('var', fn() => self::TEST_VALUE_1),
        ]);
    }
}
