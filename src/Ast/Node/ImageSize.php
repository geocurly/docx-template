<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;

class ImageSize extends Node
{
    private string $width;
    private string $height;
    private ?bool $ratio;

    public function __construct(NodePosition $position, string $width, string $height, ?bool $ratio = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->ratio = $ratio;

        parent::__construct($position);
    }

    /**
     * Get width of image with measure
     * @return string
     */
    public function getWidth(): string
    {
        return $this->width;
    }

    /**
     * Get height of image with measure
     * @return string
     */
    public function getHeight(): string
    {
        return $this->height;
    }

    /**
     * If we need save image ratio
     * @return bool|null
     */
    public function isSaveRatio(): ?bool
    {
        return $this->ratio;
    }

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'position' => $this->getPosition()->toArray(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'isSaveRatio' => $this->isSaveRatio(),
        ];
    }
}
