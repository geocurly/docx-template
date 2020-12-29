<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Exception\Lexer\SyntaxError;
use DocxTemplate\Exception\Processor\ResourceOpenException;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Lexer\Lexer;
use DocxTemplate\Processor\Process\Process;
use DocxTemplate\Processor\Source\Docx;
use DocxTemplate\Processor\Source\Relations;
use Psr\Http\Message\StreamInterface;

class TemplateProcessor
{
    private Docx $docx;
    private BindStore $store;

    /**
     * TemplateProcessor constructor.
     * @param string $source
     * @param BindStore $store
     * @throws ResourceOpenException
     */
    public function __construct(string $source, BindStore $store)
    {
        $this->docx = new Docx($source);
        $this->store = $store;
    }

    /**
     * Run template processing
     *
     * @return iterable
     * @throws SyntaxError
     * @throws TemplateException
     */
    public function run(): iterable
    {
        foreach ($this->docx->getRelations() as $main => $relations) {
            yield $main => $this->process($main, $relations);

            foreach ($relations->getFiles() as $file) {
                yield $file => $this->process($file, $relations);
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
     * @return string|StreamInterface
     * @throws ResourceOpenException
     * @throws SyntaxError
     */
    private function process(string $name, Relations $relations) /*: string|StreamInterface */
    {
        $content = $this->docx->get($name);
        $process = new Process($this->store, new Lexer($content));
        return $process->run($content);
    }
}
