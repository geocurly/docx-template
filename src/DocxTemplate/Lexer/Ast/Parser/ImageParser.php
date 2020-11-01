<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Ast\Parser;

use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\Image;
use DocxTemplate\Lexer\Contract\Ast\AstNode;
use DocxTemplate\Lexer\Exception\SyntaxError;

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
            throw new SyntaxError("Image couldn't have an argument.");
        }

        $next = $this->firstNotEmpty($id->getPosition()->getEnd());
        if ($next === null) {
            throw new SyntaxError("Unclosed image");
        }

        if ($next->getFound() === self::BLOCK_END) {
            // There is an image without given size
            // This same as Identity node
            return new $id;
        }

        if ($next->getFound() === self::IMAGE_SIZE_DELIMITER) {
            $size = $this->imageSize($next->getEnd());
            if ($size === null) {
                throw new SyntaxError("Unknown image size");
            }
        } else {
            throw new SyntaxError("Unexpected image delimiter: {$next->getFound()}");
        }

        return new Image($id, $size);
    }
}