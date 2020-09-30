<?php

require __DIR__. "/../vendor/autoload.php";

//$zip = new \DocxTemplate\Lexer\Source\Docx('template.docx', 'tmp.docx');
$zip = new class implements \DocxTemplate\Lexer\Contract\SourceInterface {

    public function getStreams(): iterable
    {
        yield "nested" => \GuzzleHttp\Psr7\stream_for(
            <<<'DOCX'
             asdas   $<asd>{  ${some} ? ${<tag>nested `var`</tag>} : `$var in string`}    
            DOCX
        );
    }
};

$lex = new \DocxTemplate\Lexer\Lexer($zip);
dd($lex->parse());