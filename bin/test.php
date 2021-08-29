<?php

use DocxTemplate\Processor\BaseFactory;
use DocxTemplate\Processor\Process\Bind\ImageBind;
use DocxTemplate\Processor\Process\Bind\ValuableBind;
use DocxTemplate\Processor\Source\Docx;
use DocxTemplate\Processor\Template;
use DocxTemplate\Processor\DocxProcessor;

require_once "vendor/autoload.php";

$binds = [
    new class extends ImageBind {

        public function getId(): string
        {
            return 'img';
        }

        public function getValue(): string
        {
            return 'tests/Fixture/Image/cat.jpeg';
        }
    },
];

$templates = [
    'instead_str' => 'Взамен',
    'notification_date' => '25.01.1993',
    'date' => '1993-01-17 23:01:01',
    'doc_num' => ' Номер',
    'with_respect' => ' citizen_data_list',
    'citizen_data_list' => ' citizen_data_list',
    'doc_reg_date' => '1993-01-17 23:01:01',
    'organizations_to_str' => 'таким и таким'
];

foreach ($templates as $id => $str) {
    $binds[] = new class($id, $str) extends ValuableBind {
        public function __construct(private string $id, private string $str)
        {
        }

        public function getId(): string
        {
            return $this->id;
        }

        public function getValue(): string
        {
            return $this->str;
        }
    };
}

$template = new Template(
    new DocxProcessor(
        new Docx('/tmp/template.docx'),
        new BaseFactory(...$binds),
    )
);

$template->stream('tmp.docx');

