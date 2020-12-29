<?php

use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Processor\BindStore;
use DocxTemplate\Processor\Template;
use DocxTemplate\Processor\TemplateProcessor;

require_once "vendor/autoload.php";

class Name implements Valuable
{
    public function getId(): string
    {
        return 'name';
    }

    public function getValue(): string
    {
        return 'There is name!';
    }

    public function getType(): string
    {
        return 'Identity';
    }
}

$store = new BindStore([
    new Name(),
]);

$template = new Template(
    new TemplateProcessor(
        'template.docx',
        $store
    )
);

$template->stream('tmp.docx');

