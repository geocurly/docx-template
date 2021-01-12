<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;


use DocxTemplate\Exception\Processor\ResourceOpenException;
use DocxTemplate\Lexer\Enum\ImageDimension;

final class Image
{
    private const TEMPLATE = <<<XML
    <w:pict>
       <v:shape type="#_x0000_t75" style="width:%s;height:%s">
            <v:imagedata r:id="%s" o:title=""/>
        </v:shape>
    </w:pict>
    XML;

    private const EXTENSIONS = [
        'image/jpeg' => 'jpeg',
        'image/png'  => 'png',
        'image/bmp'  => 'bmp',
        'image/gif'  => 'gif',
    ];
    /**
     * @var Relation
     */
    private Relation $relation;
    private string $width;
    private string $height;
    private string $ext;

    public function __construct(Relation $relation, ?string $width, ?string $height, ?bool $isSaveRatio)
    {
        if (is_numeric($width)) {
            $width .= ImageDimension::PX;
        }

        if (is_numeric($height)) {
            $height .= ImageDimension::PX;
        }

        $this->relation = $relation;
        $this->init($width, $height, $isSaveRatio ?? false);
    }

    /**
     * Build image xml
     * @return string
     */
    public function getXml(): string
    {
        $xml = sprintf(
            self::TEMPLATE,
            $this->width,
            $this->height,
            $this->relation->getId(),
        );

        return preg_replace('/>\s+</', '><', $xml);
    }

    /**
     * Get extension of the image
     * @return string
     */
    public function getExtension(): string
    {
        return $this->ext;
    }

    /**
     * Get relation
     * @return Relation
     *
     * @codeCoverageIgnore
     */
    public function getRelation(): Relation
    {
        return $this->relation;
    }

    /**
     * Fix image dimension
     *
     * @param string|null $width
     * @param string|null $height
     * @param bool $isSaveRatio
     * @return void
     * @throws ResourceOpenException
     */
    private function init(?string $width, ?string $height, bool $isSaveRatio): void
    {
        $imageData = @getimagesize($this->relation->getUrl());
        if (!is_array($imageData)) {
            throw new ResourceOpenException("Invalid image: {$this->relation->getUrl()}");
        }

        [$actualWidth, $actualHeight, $imageType] = $imageData;

        $mime = image_type_to_mime_type($imageType);
        if (!array_key_exists($mime, self::EXTENSIONS)) {
            throw new ResourceOpenException("Invalid mime: {$mime} in {$this->relation->getUrl()}");
        }

        if ($isSaveRatio) {
            $imageRatio = $actualWidth / $actualHeight;

            if ($width === null && $height === null) { // defined size are empty
                $width = $actualWidth . ImageDimension::PX;
                $height = $actualHeight . ImageDimension::PX;
            } elseif ($width === null) { // defined width is empty
                $heightFloat = (float) $height;
                $widthFloat = number_format($heightFloat * $imageRatio, 2);
                $matches = [];
                preg_match("/\d([a-z%]+)$/", $height, $matches);
                $width = $widthFloat . $matches[1];
            } elseif ($height === null) { // defined height is empty
                $widthFloat = (float) $width;
                $heightFloat = number_format($widthFloat / $imageRatio, 2);
                $matches = [];
                preg_match("/\d([a-z%]+)$/", $width, $matches);
                $height = $heightFloat . $matches[1];
            } else { // we have defined size, but we need also check it aspect ratio
                preg_match("/\d([a-z%]+)$/", $width, $widthMatches);
                preg_match("/\d([a-z%]+)$/", $height, $heightMatches);
                // try to fix only if dimensions are same
                if ($widthMatches[1] === $heightMatches[1]) {
                    $dimension = $widthMatches[1];
                    $widthFloat = (float) $width;
                    $heightFloat = (float) $height;
                    $definedRatio = $widthFloat / $heightFloat;

                    if ($imageRatio > $definedRatio) { // image wider than defined box
                        $height = number_format($widthFloat / $imageRatio, 2) . $dimension;
                    } elseif ($imageRatio < $definedRatio) { // image higher than defined box
                        $width = number_format($heightFloat * $imageRatio, 2) . $dimension;
                    }
                }
            }
        }

        $this->width = $width ?? ($actualWidth . ImageDimension::PX);
        $this->height = $height ?? ($actualHeight . ImageDimension::PX);
        $this->ext = self::EXTENSIONS[$mime];
    }
}
