<?php

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\SourceInterface;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

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
                '${variable}' => [
                    'type' => Lexer::TYPE_SIMPLE_VARIABLE,
                    'files' => [
                        'simple' => [
                            [22, 11],
                            [46, 11],
                        ],
                        'easy' => [
                            [18, 11],
                        ],
                        'tags' => [
                            [0, 24] // There is more chars due stripped tags
                        ]
                    ]
                ],
                '${etc}' => [
                    'type' => Lexer::TYPE_SIMPLE_VARIABLE,
                    'files' => [
                        'simple' => [
                            [38, 6]
                        ]
                    ]
                ]
            ],
            $lexer->parse()
        );
    }

    private function getSimpleSource(): SourceInterface
    {
        return new class implements SourceInterface {

            public function getStreams(): iterable
            {
                yield "simple" => stream_for(<<<'DOCX'
                    Some simple text with ${variable} and ${etc}. ${variable}   
                    DOCX);

                yield "easy" => stream_for(<<<'DOCX'
                    Another text with ${variable}
                    DOCX);

                yield "tags" => stream_for(<<<'DOCX'
                    ${<tags>variable</tags>}
                    DOCX);
            }
        };
    }

}
