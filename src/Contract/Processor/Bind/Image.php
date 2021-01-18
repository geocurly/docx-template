<?php

declare(strict_types=1);

namespace DocxTemplate\Contract\Processor\Bind;

interface Image extends Valuable
{
    /**
     * Get image width
     * @return string|null
     */
    public function getWidth(): ?string;

    /**
     * Get image height
     * @return string|null
     */
    public function getHeight(): ?string;

    /**
     * Is need save image ratio
     * @return bool|null
     */
    public function isSaveRatio(): ?bool;
}
