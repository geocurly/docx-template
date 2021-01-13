<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Exception\Processor\ResourceOpenException;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Lexer\Lexer;
use DocxTemplate\Processor\Process\Resolver;
use DocxTemplate\Processor\Source\Docx;
use Psr\Http\Message\StreamInterface;

class TemplateProcessor
{
    private Docx $docx;
    private BindFactory $factory;

    /**
     * TemplateProcessor constructor.
     * @param string $source
     * @param BindFactory $factory
     * @throws ResourceOpenException
     */
    public function __construct(string $source, BindFactory $factory)
    {
        $this->docx = new Docx($source);
        $this->factory = $factory;
    }

    /**
     * Run template processing
     *
     * @return iterable
     * @throws SyntaxErrorException
     * @throws TemplateException
     */
    public function run(): iterable
    {
        $relations = $this->docx->getDocumentRelations();
        yield $relations->getOwnerPath() => $this->process($relations->getOwnerPath());

        foreach ($relations->getFiles() as $file) {
            yield $file => $this->process($file);
        }

        yield $relations->getPath() => $relations->getXml();

        yield from $this->docx->flush();
    }

    /**
     * Start template processing
     *
     * @param string $name file to process
     * @return string|StreamInterface
     * @throws ResourceOpenException
     * @throws SyntaxErrorException
     */
    private function process(string $name) /*: string|StreamInterface */
    {
        $content = $this->docx->get($name);
        $lexer = new Lexer($content);
        $resolver = new Resolver($this->factory);
        foreach ($lexer->run() as $node) {
            $decision = $resolver->solve($node, $this->docx->getRelation($name));

            $content = substr_replace(
                $content,
                $decision->getValue(),
                $node->getPosition()->getStart(),
                $node->getPosition()->getLength(),
            );
        }

        return $content;
    }
}
