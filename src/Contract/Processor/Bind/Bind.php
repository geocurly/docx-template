<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface Bind
{
    /**
     * Identity of a bind
     * @return string
     */
    public function getId(): string;
}
