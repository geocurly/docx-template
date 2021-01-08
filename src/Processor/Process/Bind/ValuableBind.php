<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process\Bind;

use DocxTemplate\Contract\Processor\Bind\Valuable;

/** @codeCoverageIgnore  */
abstract class ValuableBind implements Valuable
{
    use ParametersTrait;
}