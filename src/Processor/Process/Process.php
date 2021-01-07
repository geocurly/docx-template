<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Lexer\Lexer;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Exception\Lexer\SyntaxError;
use DocxTemplate\Exception\Processor\TemplateException;

class Process
{
    private BindFactory $factory;
    private Lexer $lexer;

    public function __construct(BindFactory $factory, Lexer $lexer)
    {
        $this->factory = $factory;
        $this->lexer = $lexer;
    }

    /**
     * Start template processing
     *
     * @param string $content
     * @return string
     * @throws SyntaxError
     * @throws TemplateException
     */
    public function run(string $content): string
    {
        $resolver = new Resolver($this->factory);
        /** @var Node $node */
        foreach ($this->lexer->run() as $node) {
            $content = substr_replace(
                $content,
                $resolver->solve($node),
                $node->getPosition()->getStart(),
                $node->getPosition()->getLength(),
            );
        }

        return $content;
    }
}
