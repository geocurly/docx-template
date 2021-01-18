<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process\Bind;

use DocxTemplate\Contract\Processor\Bind\Table as TableInterface;

abstract class Table implements TableInterface
{
    use ParametersTrait;
}
