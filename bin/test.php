<?php

require __DIR__. "/../vendor/autoload.php";

$zip = file_get_contents("zip://template.docx#word/document.xml");

$lex = new \DocxTemplate\Lexer\Lexer($zip);
foreach ($lex->parse() as $scope) {
    dump($scope);
}