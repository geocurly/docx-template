<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\FilterExpression;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\Str;
use DocxTemplate\Lexer\Ast\Parser\ExpressionParser;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\InvalidSourceException;
use DocxTemplate\Lexer\Exception\SyntaxError;
use DocxTemplate\Tests\Lexer\Common\ReaderTrait;
use PHPUnit\Framework\TestCase;

class ExpressionParserTest extends TestCase
{
    use ReaderTrait;

    /**
     * @covers       \DocxTemplate\Lexer\Ast\Parser\ExpressionParser::parse
     * @dataProvider positiveProvider
     *
     * @param string $content
     * @param AstNode $left
     * @param AstNode|null $expected
     * @throws InvalidSourceException
     * @throws SyntaxError
     */
    public function testParsePositive(string $content, AstNode $left, ?AstNode $expected): void
    {
        foreach ($this->reader($content) as $reader) {
            $this->assertEquals(
                $expected,
                (new ExpressionParser($reader, $left))->parse(),
                "Try to find expression in '$content'. " . get_class($reader)
            );
        }
    }

    public function positiveProvider(): array
    {
        $filter = function ($left, $right) {
            return new FilterExpression($left, $right);
        };

        $id = function ($id, $from, $length) {
            return new Identity($id, $this->pos($from, $length));
        };

        return [
            [
                ' ${ var | filter } ',
                $id('var', 4, 3),
                $filter($id('var', 4, 3), $id('filter', 10, 6))
            ],
            [
                '${ target(`1`) | filter }',
                $call = new Call(
                    $id('target', 3, 6),
                    $this->pos(3, 11),
                    new Str($this->pos(10, 3))
                ),
                $filter($call, $id('filter', 17, 6))
            ],
            [
                ' ${ var | filter( `string ${var}`  ,  ${var} )}',
                $id('var', 4, 3),
                $filter(
                    $id('var', 4, 3),
                    new Call(
                        $id('filter', 10, 6),
                        $this->pos(10, 36),
                        new Str(
                            $this->pos(18, 15),
                            new Block(
                                $this->pos(26, 6),
                                $id('var', 28, 3),
                            )
                        ),
                        new Block(
                            $this->pos(38, 6),
                            $id('var', 40, 3)
                        )
                    )
                )
            ]
        ];
    }
}
