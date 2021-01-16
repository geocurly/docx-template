<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Contract\Processor\Source\RelationContainer;

final class ProcessFactory
{
    /**
     * @param RelationContainer $container
     * @return iterable<SimpleContentProcess>
     */
    public function make(RelationContainer $container): iterable
    {
        $content = $container->getContent();
        preg_match_all('/<w:tr[^<]*>.*<\/w:tr>/U', $content, $matches, PREG_OFFSET_CAPTURE);

        $offset = 0;
        foreach ($matches[0] ?? [] as [$row, $position]) {
            yield new SimpleContentProcess(
                substr($content, $offset, $position - $offset),
                $container
            );

            yield new SimpleContentProcess($row, $container);

            $offset = $position + strlen($row);
        }

        yield new SimpleContentProcess(substr($content, $offset), $container);
    }
}
