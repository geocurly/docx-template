<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Lexer\Common;


use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\Condition;
use DocxTemplate\Lexer\Ast\Node\FilterExpression;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Ast\Node\Image;
use DocxTemplate\Lexer\Ast\Node\ImageSize;
use DocxTemplate\Lexer\Ast\Node\Str;
use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

trait AstNodeTrait
{
    protected static function block(int $from, int $length, AstNode ...$nested): Block
    {
        return new Block(
             new NodePosition($from, $length),
            ...$nested
        );
    }

    protected static function id(string $id, int $from, int $length): Identity
    {
        return new Identity($id, new NodePosition($from, $length));
    }

    protected static function call(Identity $id, int $from, int $length, AstNode ...$params): Call
    {
        return new Call($id, new NodePosition($from, $length), ... $params);
    }

    protected static function str(int $from, int $length, AstNode ...$nested): Str
    {
        return new Str(new NodePosition($from, $length), ...$nested);
    }

    protected static function imageSize(int $from, int $len, string $wid, string $hei, bool $ratio = null): ImageSize
    {
        return new ImageSize(
            new NodePosition($from, $len),
            $wid,
            $hei,
            $ratio
        );
    }

    protected static function image(AstNode $id, ImageSize $size): Image
    {
        return new Image($id, $size);
    }

    protected static function filter(AstNode $left, AstNode $right): FilterExpression
    {
        return new FilterExpression($left, $right);
    }

    protected static function cond(AstNode $if, AstNode $then, AstNode $else): Condition
    {
        return new Condition($if, $then, $else);
    }
}
