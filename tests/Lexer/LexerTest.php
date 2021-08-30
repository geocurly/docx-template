<?php

namespace DocxTemplate\Tests\Lexer;

use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Lexer\Lexer;
use DocxTemplate\Tests\Common\NodeTrait;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Lexer\Lexer
 */
class LexerTest extends TestCase
{
    use NodeTrait;

    /**
     * @dataProvider getRunProvider
     * @param $content
     * @param array $blocks
     * @throws SyntaxErrorException
     */
    public function testSimpleParse($content, array $blocks): void
    {
        foreach ([$content, Utils::streamFor($content)] as $key => $source) {
            $lexer = new Lexer();
            $objects = [];
            foreach ($lexer->run($source) as $num => $block) {
                $objects[] = $block;
            }

            self::assertTrue($this->isObjectsSame($objects, $blocks));
        }
    }

    public function getRunProvider(): array
    {
        $cond = self::block(
            30,
            19,
            '${ if ? if : else }',
            self::cond(
                self::id('if', 33, 2),
                self::id('if', 38, 2),
                self::id('else', 43, 4),
            ),
        );

        $filter = self::block(
            58,
            17,
            '${ var | filter }',
            self::filter(
                self::id('var', 61, 3),
                self::id('filter', 67, 6),
            ),
        );

        $img = self::block(
            107,
            15,
            '${image:150x10}',
            self::image(
                self::id('image', 109, 5),
                self::imageSize(115, 6, '150', '10'),
            ),
        );

        $cond->getPosition()->addNext($filter->getPosition());
        $filter->getPosition()->addNext($img->getPosition());

        return [
            [
                <<<'XML'
                <document>
                    <text>
                        ${ if ? if : else }
                        ${ var | filter }
                    </text>
                    <text>
                        ${image:150x10}
                    </text>
                </document>
                XML,
                [
                    $cond,
                    $filter,
                    $img,
                ]
            ],
        ];
    }
}
