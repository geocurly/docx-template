<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process\Bind;

use DocxTemplate\Contract\Processor\Bind\Valuable;

/** @codeCoverageIgnore  */
abstract class Bind implements Valuable
{
    use ParametersTrait;
}