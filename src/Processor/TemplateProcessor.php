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
use DocxTemplate\Processor\Source\Relations;
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
        yield $relations->getOwnerPath() => $this->process($relations->getOwnerPath(), $relations);

        foreach ($relations->getFiles() as $file) {
            yield $file => $this->process($file, $this->docx->getRelation($file));
        }

        foreach ($relations->getUnprocessed() as $image) {
            yield $image->getSourcePath() => file_get_contents($image->getUrl());
        }

        yield $relations->getPath() => $relations->getXml();

        yield from $this->docx->flush();
    }

    /**
     * Start template processing
     *
     * @param string $name file to process
     * @param Relations $relations
     * @return string|StreamInterface
     * @throws ResourceOpenException
     * @throws SyntaxErrorException
     */
    private function process(string $name, Relations $relations) /*: string|StreamInterface */
    {
        $content = $this->docx->get($name);
        $lexer = new Lexer($content);
        $resolver = new Resolver($this->factory);
        foreach ($lexer->run() as $node) {
            $decision = $resolver->solve($node, $relations);

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
