<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Exception\Lexer\SyntaxErrorException;
use DocxTemplate\Exception\Processor\ResourceOpenException;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Lexer\Lexer;
use DocxTemplate\Processor\Process\Process;
use DocxTemplate\Processor\Source\ContentTypes;
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
        $types = $this->docx->getContentTypes();
        foreach ($this->docx->getRelations() as $main => $relations) {
            yield $main => $this->process($main, $relations, $types);

            foreach ($relations->getFiles() as $file) {
                yield $file => $this->process($file, $relations, $types);
            }

            yield $relations->getName() => $relations->getXml();
        }

        yield from $this->docx->flush();
    }

    /**
     * Start template processing
     *
     * @param string $name file to process
     * @param Relations $relations
     * @param ContentTypes $types
     * @return string|StreamInterface
     * @throws ResourceOpenException
     * @throws SyntaxErrorException
     * @throws TemplateException
     */
    private function process(string $name, Relations $relations, ContentTypes $types) /*: string|StreamInterface */
    {
        $content = $this->docx->get($name);
        $process = new Process(
            $this->factory,
            new Lexer($content),
            $relations,
            $types
        );

        return $process->run($content);
    }
}
