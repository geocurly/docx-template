<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Contract\Lexer\Lexer as LexerInterface;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Lexer\Lexer;
use DocxTemplate\Processor\Process\Resolver;
use DocxTemplate\Processor\Source\Docx;
use Psr\Http\Message\StreamInterface;

class TemplateProcessor
{
    private Docx $docx;
    private BindFactory $factory;
    private LexerInterface $lexer;

    /**
     * TemplateProcessor constructor.
     * @param BindFactory $factory
     * @param Docx $docx
     * @param LexerInterface|null $lexer
     */
    public function __construct(Docx $docx, BindFactory $factory, LexerInterface $lexer = null)
    {
        $this->docx = $docx;
        $this->factory = $factory;
        $this->lexer = $lexer ?? new Lexer();
    }

    /**
     * Run template processing
     *
     * @return iterable
     * @throws SyntaxErrorException
     */
    public function run(): iterable
    {
        foreach ($this->docx->getPreparedFiles() as $path => $source) {
            yield $path => $this->process($source, $source->getContent());
        }

        yield from $this->docx->getLeftoverFiles();
    }

    /**
     * Start template processing
     *
     * @param RelationContainer $container
     * @param string $content
     * @return string|StreamInterface
     * @throws SyntaxErrorException
     */
    private function process(RelationContainer $container, string $content): string
    {
        foreach ($this->lexer->run($content) as $node) {
            $resolver = new Resolver($this->factory, $container);
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
