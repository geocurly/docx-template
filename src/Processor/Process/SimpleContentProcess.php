<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Contract\Processor\Process;
use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Lexer\Lexer;

class SimpleContentProcess implements Process
{
    public function __construct(
        private string $content,
        private RelationContainer $container,
    ) {
    }

    protected function getContent(): string
    {
        return $this->content;
    }

    protected function getContainer(): RelationContainer
    {
        return $this->container;
    }

    /** @inheritdoc  */
    public function run(BindFactory $factory): string
    {
        $lexer = new Lexer();
        $content = $this->getContent();
        $resolver = new Resolver($factory, $this->getContainer());
        foreach ($lexer->run($content) as $node) {
            $decision = $resolver->solve($node);

            $position = $node->getPosition();
            $replaced = $decision->getValue();
            $content = substr_replace(
                $content,
                $replaced,
                $position->getStart(),
                $position->getLength(),
            );

            $position->change(
                $position->getStart(),
                strlen($replaced),
            );
        }

        return $content;
    }
}
