<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Contract\Lexer\Lexer;
use DocxTemplate\Exception\Lexer\SyntaxError;
use DocxTemplate\Processor\BindStore;

class Process
{
    private BindStore $store;
    private Lexer $lexer;

    public function __construct(BindStore $store, Lexer $lexer)
    {
        $this->store = $store;
        $this->lexer = $lexer;
    }

    /**
     * Start template processing
     *
     * @param string $content
     * @return string
     * @throws SyntaxError
     */
    public function run(string $content): string
    {
        foreach ($this->lexer->run() as $node) {
            if ($node instanceof Block) {
                $nodeContent = [];
                foreach ($node->nested() as $nested) {
                    if ($nested instanceof Identity) {
                        $value = $this->store->get($nested)->getValue();
                        $nodeContent[] = $value;
                    }
                }

                $position = $node->getPosition();
                $content = substr_replace(
                    $content,
                    implode(' ', $nodeContent),
                    $position->getStart(),
                    $position->getLength()
                );
            }
        }

        return $content;
    }
}
