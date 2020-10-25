<?php

namespace DocxTemplate\Lexer;

use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    /**
     * @covers \DocxTemplate\Lexer\Lexer::parse
     */
    public function testSimpleParse(): void
    {
        $lexer = new Lexer($this->getSimpleSource());
        $this->assertSame(
            [
            ],
            $lexer->parse()
        );
    }
}
