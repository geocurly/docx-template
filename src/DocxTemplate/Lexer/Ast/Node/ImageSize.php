<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Node;

use DocxTemplate\Lexer\Ast\NodePosition;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

class ImageSize implements AstNode
{
    private NodePosition $position;
    private string $width;
    private string $height;
    private ?bool $ratio;

    public function __construct(NodePosition $position, string $width, string $height, ?bool $ratio = null)
    {
        $this->position = $position;
        $this->width = $width;
        $this->height = $height;
        $this->ratio = $ratio;
    }
}