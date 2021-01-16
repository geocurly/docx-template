<?php

use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Processor\Process\Bind\ImageBind;
use DocxTemplate\Processor\Process\Bind\ValuableBind;
use DocxTemplate\Processor\Process\Bind\Filter\Date as DateFilter;
use DocxTemplate\Processor\Source\Docx;
use DocxTemplate\Processor\Template;
use DocxTemplate\Processor\DocxProcessor;

require_once "vendor/autoload.php";

$factory = new class implements BindFactory
{
    private array $filters = [];
    private array $variables = [];
    private array $images = [];

    public function __construct()
    {
        $this->variables['date'] = function () {
            return new class extends ValuableBind {

                public function getId(): string
                {
                    return 'date';
                }

                public function getValue(): string
                {
                    return '1993-01-17 23:01:01';
                }
            };
        };

        $this->filters['date'] = function () {
            return new DateFilter('date');
        };

        $this->variables['img'] = function () {
            return new class extends ImageBind {

                public function getId(): string
                {
                    return 'img';
                }

                public function getValue(): string
                {
                    return 'tests/Fixture/Image/cat.jpeg';
                }
            };
        };
    }

    /** @inheritdoc  */
    public function valuable(string $name): Valuable
    {
        return $this->variables[$name]();
    }

    /** @inheritdoc  */
    public function filter(string $name): Filter
    {
        return $this->filters[$name]();
    }
};



$template = new Template(
    new DocxProcessor(new Docx('template.docx'), $factory)
);

$template->stream('tmp.docx');

