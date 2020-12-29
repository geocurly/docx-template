<?php

use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Processor\BindStore;
use DocxTemplate\Processor\Process\Bind\Filter\Date as DateFilter;
use DocxTemplate\Processor\Template;
use DocxTemplate\Processor\TemplateProcessor;

require_once "vendor/autoload.php";

class Date implements Valuable
{
    public function getId(): string
    {
        return 'date';
    }

    public function getValue(): string
    {
        return '1993-01-17 23:01:01';
    }
}

$store = new BindStore([
    new Date(),
    new DateFilter(),
]);

$template = new Template(
    new TemplateProcessor(
        'template.docx',
        $store
    )
);

$template->stream('tmp.docx');

