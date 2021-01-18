<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Contract\Processor\Process;
use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Lexer\Lexer;

class SimpleContentProcess implements Process
{
    private string $content;
    private RelationContainer $container;

    public function __construct(string $content, RelationContainer $container)
    {
        $this->content = $content;
        $this->container = $container;
    }

    /** @inheritdoc  */
    public function run(BindFactory $factory): string
    {
        $lexer = new Lexer();
        $content = $this->content;
        foreach ($lexer->run($content) as $node) {
            $resolver = new Resolver($factory, $this->container);
            $decision = $resolver->solve($node);

            $position = $node->getPosition();
            $replaced = $decision->getValue();
            $content = substr_replace(
                $content,
                $replaced,
                $position->getStart(),
                $position->getLength(),
            );

            $position->change($position->getStart(), strlen($replaced));
        }

        return $content;
    }
}
