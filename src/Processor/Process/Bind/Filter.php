<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process\Bind;

use DocxTemplate\Contract\Processor\Bind\Filter as FilterInterface;

abstract class Filter implements FilterInterface
{
    use ParametersTrait;
}