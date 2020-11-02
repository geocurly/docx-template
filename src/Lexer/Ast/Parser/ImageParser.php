<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\Image;
use DocxTemplate\Lexer\Ast\Parser\Exception\EndNotFoundException;
use DocxTemplate\Lexer\Ast\Parser\Exception\UnsupportedArgumentException;
use DocxTemplate\Lexer\Contract\Ast\AstNode;

class ImageParser extends Parser
{
    /** @inheritdoc  */
    public function parse(): ?AstNode
    {
        $offset = $this->getOffset();
        $id = $this->identity($offset);
        if ($id === null) {
            return null;
        }

        if ($id instanceof Call) {
            throw new UnsupportedArgumentException("Image couldn't have any argument.");
        }

        $size = $this->imageSize($id);
        if ($size !== null) {
            return new Image($id, $size);
        }

        $next = $this->firstNotEmpty($id->getPosition()->getEnd());
        if ($next->getFound() !== self::BLOCK_END) {
            throw new EndNotFoundException('');
        }

        // There is an image without given size
        // This same as Identity node
        return new $id;
    }
}