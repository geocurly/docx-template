<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Lexer\Lexer;

final class TableContentProcess extends SimpleContentProcess
{
    /** @inheritdoc  */
    public function run(BindFactory $factory): string
    {
        $lexer = new Lexer();
        $content = $this->getContent();
        $deferred = [];
        foreach ($lexer->run($content) as $node) {
            $resolver = new Resolver($factory, $this->getContainer());
            $decision = $resolver->solve($node);
            $deferred[] = [$node->getPosition(), $decision->getValue()];
        }

        foreach ($deferred as [$position, $replaced]) {
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
