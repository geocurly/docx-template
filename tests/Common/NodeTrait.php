<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Common;

use DocxTemplate\Ast\Node\Block;
use DocxTemplate\Ast\Node\Call;
use DocxTemplate\Ast\Node\Condition;
use DocxTemplate\Ast\Node\EscapedBlock;
use DocxTemplate\Ast\Node\EscapedChar;
use DocxTemplate\Ast\Node\FilterExpression;
use DocxTemplate\Ast\Node\Identity;
use DocxTemplate\Ast\Node\Image;
use DocxTemplate\Ast\Node\ImageSize;
use DocxTemplate\Ast\Node\Str;
use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Ast\Identity as IdentityInterface;

trait NodeTrait
{
    protected static function escapedBlock(int $from, int $length, string $content, Node ...$nested): Block
    {
        return new EscapedBlock(
            new NodePosition($from, $length),
            $content,
            ...$nested
        );
    }

    protected static function block(int $from, int $length, string $content, Node ...$nested): Block
    {
        return new Block(
             new NodePosition($from, $length),
            $content,
            ...$nested
        );
    }

    protected static function id(string $id, int $from, int $length): Identity
    {
        return new Identity($id, new NodePosition($from, $length));
    }

    protected static function call(Identity $id, int $from, int $length, Node ...$params): Call
    {
        return new Call($id, new NodePosition($from, $length), ... $params);
    }

    protected static function str(int $from, int $length, string $content, Node ...$nested): Str
    {
        return new Str(new NodePosition($from, $length), $content, ...$nested);
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

    protected static function image(IdentityInterface $id, ImageSize $size): Image
    {
        return new Image($id, $size);
    }

    protected static function filter(Node $left, Node $right): FilterExpression
    {
        return new FilterExpression($left, $right);
    }

    protected static function cond(Node $if, Node $then, Node $else): Condition
    {
        return new Condition($if, $then, $else);
    }

    protected static function escaped(int $from, int $to, string $content): EscapedChar
    {
        return new EscapedChar(new NodePosition($from, $to), $content);
    }
}
