<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Lexer\Lexer;
use DocxTemplate\Processor\BindStore;

class Process
{
    private BindStore $store;

    public function __construct(BindStore $store)
    {
        $this->store = $store;
    }

    public function run(string $content): string
    {
        $lexer = new Lexer($content);
        foreach ($lexer->parse() as $node) {
            if ($node instanceof Block) {
                $nodeContent = [];
                foreach ($node->nested() as $nested) {
                    if ($nested instanceof Identity) {
                        $value = $this->store->getVariable($nested->getId());
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
