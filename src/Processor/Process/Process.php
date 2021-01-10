<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Lexer\Lexer;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Processor\Source\Relations;

class Process
{
    private BindFactory $factory;
    private Lexer $lexer;
    private Relations $relations;

    public function __construct(BindFactory $factory, Lexer $lexer, Relations $relations)
    {
        $this->factory = $factory;
        $this->lexer = $lexer;
        $this->relations = $relations;
    }

    /**
     * Start template processing
     *
     * @param string $content
     * @return string
     * @throws SyntaxErrorException
     * @throws TemplateException
     */
    public function run(string $content): string
    {
        $resolver = new Resolver($this->factory, $this->relations);
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
