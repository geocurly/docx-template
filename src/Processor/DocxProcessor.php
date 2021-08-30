<?php

declare(strict_types=1);

namespace DocxTemplate\Processor;

use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Contract\Processor\Source\Source;
use DocxTemplate\Processor\Process\ProcessFactory;
use Psr\Http\Message\StreamInterface;

final class DocxProcessor
{
    public function __construct(
        private Source $source,
        private BindFactory $factory,
    ) {
    }

    /**
     * Run template processing
     *
     * @return iterable<string, string|StreamInterface|resource>
     */
    public function run(): iterable
    {
        $maker = new ProcessFactory();
        foreach ($this->source->getPreparedFiles() as $path => $source) {
            $parts = [];
            foreach ($maker->make($source) as $process) {
                $parts[] = $process->run($this->factory);
            }

            yield $path => implode('', $parts);
        }

        yield from $this->source->getLeftoverFiles();
    }
}
