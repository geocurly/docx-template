<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer;

use DocxTemplate\Lexer\Contract\ReaderInterface;
use DocxTemplate\Lexer\Exception\LexerException;
use DocxTemplate\Lexer\Exception\SyntaxError;

class Macro
{
    public const MACRO_BRACE_OPEN = '${';
    public const MACRO_BRACE_CLOSE = '}';

    public const VARIABLE_OPEN = '$';
    public const STRING_BRACE = '`';

    private function __construct()
    {
    }

    public static function find(ReaderInterface $reader, int $position, string $source): ?self
    {
        $open = $reader->find(self::MACRO_BRACE_OPEN, $position);
        if ($open === null) {
            return null;
        }

        $lastPosition = array_sum($open);
        do {
            $any = $reader->firstNotEmpty([self::VARIABLE_OPEN, self::STRING_BRACE], 0);
            if ($any === null) {
                // There is simple variable
                break;
            }

            switch ($any[0]) {
                case "$":
                    $nested = null; //There is variable
                    break;
                case "`";
                    $nested = null; //There is string
                    break;
                default:
                    throw new LexerException("Unexpected first character: {$any[0]}");
            }

            if ($nested === null) {
                throw new SyntaxError("Can't parse nested construction");
            }

            $nestedEnd = 0;
            $ternaryChar = $reader->firstNotEmpty(['?'], $nestedEnd);
            if ($ternaryChar === null) {
                $lastPosition = $nestedEnd;
                break;
            }
        }
        while (false);

        $close = $reader->find(self::MACRO_BRACE_CLOSE, $lastPosition);
        if ($close === null) {
            throw new SyntaxError("Unclosed macro in $source");
        }

        $macroContent = $reader->read(array_sum($open), array_sum($close) - $close[1]);
        dd($macroContent);
    }
}
