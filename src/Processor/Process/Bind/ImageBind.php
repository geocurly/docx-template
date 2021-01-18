<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process\Bind;

use DocxTemplate\Contract\Processor\Bind\Image;
use DocxTemplate\Exception\Processor\BindException;
use DocxTemplate\Lexer\Enum\ImageDimension;

/** @codeCoverageIgnore  */
abstract class ImageBind extends ValuableBind implements Image
{
    private ?string $width = null;

    private ?string $height = null;

    private ?bool $isSaveRatio = null;

    /**
     * Set width value
     *
     * @param int $width
     * @param string $dimension
     * @return $this
     * @throws BindException
     */
    final public function setWidth(int $width, string $dimension = ImageDimension::PX): self
    {
        if (!in_array($dimension, ImageDimension::DIMENSIONS, true)) {
            throw new BindException("Invalid height dimension: $dimension");
        }

        $this->width = $width . $dimension;
        return $this;
    }

    /**
     * Set height value
     *
     * @param int $height
     * @param string $dimension
     * @return $this
     * @throws BindException
     */
    final public function setHeight(int $height, string $dimension = ImageDimension::PX): self
    {
        if (!in_array($dimension, ImageDimension::DIMENSIONS, true)) {
            throw new BindException("Invalid height dimension: $dimension");
        }

        $this->height = $height . $dimension;
        return $this;
    }

    /**
     * Set if need save image ratio
     *
     * @param bool $isSaveRatio
     * @return $this
     */
    final public function setSaveRatio(bool $isSaveRatio): self
    {
        $this->isSaveRatio = $isSaveRatio;
        return $this;
    }

    /**
     * Get image width
     * @return string|null
     */
    final public function getWidth(): ?string
    {
        return $this->width;
    }

    /**
     * Get image height
     * @return string|null
     */
    final public function getHeight(): ?string
    {
        return $this->height;
    }

    /**
     * Is need save image ratio
     * @return bool|null
     */
    final public function isSaveRatio(): ?bool
    {
        return $this->isSaveRatio;
    }
}
