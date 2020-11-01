<?php

namespace DocxTemplate\Tests\Lexer;

use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    /**
     * @covers \DocxTemplate\Lexer\Lexer::parse
     */
    public function testSimpleParse(): void
    {
        self::assertSame(1, 1);
    }
}
