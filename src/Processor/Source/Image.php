<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Source;


final class Image
{
    private const TEMPLATE = <<<XML
    <w:pict>
       <v:shape type="#_x0000_t75" style="width:%s;height:%s">
            <v:imagedata r:id="%s" o:title=""/>
        </v:shape>
    </w:pict>
    XML;

    private const MIMES = [
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
    private bool $isSaveRatio;

    public function __construct(Relation $relation, string $width, string $height, bool $isSaveRatio)
    {
        $this->relation = $relation;
        $this->width = $width;
        $this->height = $height;
        $this->isSaveRatio = $isSaveRatio;
    }

    /**
     * Build image xml
     * @return string
     */
    public function build(): string
    {
        return sprintf(
            self::TEMPLATE,
            $this->width,
            $this->height,
            $this->relation->getId(),
        );
    }

    /**
     * Get relation
     * @return Relation
     */
    public function getRelation(): Relation
    {
        return $this->relation;
    }
}
