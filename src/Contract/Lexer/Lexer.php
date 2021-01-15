<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Lexer;

use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;

interface Lexer
{
    /**
     * Start parsing content and iterate blocks
     * @param $source
     * @return iterable<Node>
     * @throws SyntaxErrorException
     */
    public function run(/* string|StreamInterface */ $source): iterable;
}
