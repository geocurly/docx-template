<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process\Bind;

use DocxTemplate\Contract\Processor\Bind\Filter as FilterInterface;
use DocxTemplate\Contract\Processor\Bind\ParametersAware;

abstract class Filter implements FilterInterface, ParametersAware
{
    use ParametersTrait;
}