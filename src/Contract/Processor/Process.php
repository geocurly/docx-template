<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor;

interface Process
{
    /**
     * Run process
     * @param BindFactory $factory
     * @return string
     */
    public function run(BindFactory $factory): string;
}
