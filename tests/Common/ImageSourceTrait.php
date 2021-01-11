<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Common;

use DocxTemplate\Processor\Source\Image;
use DocxTemplate\Processor\Source\Relation;

trait ImageSourceTrait
{
    protected static function img(Relation $relation, ?string $width, ?string $height, ?bool $isSaveRatio): Image
    {
        return new Image(...func_get_args());
    }

    protected static function rel(string $url, string $id, string $target, string $type): Relation
    {
        return new Relation(...func_get_args());
    }

    protected static function imgXml(string $id, string $w, string $h): string
    {
        $xml = <<<XML
        <w:pict>
           <v:shape type="#_x0000_t75" style="width:{$w};height:{$h}">
                <v:imagedata r:id="{$id}" o:title=""/>
            </v:shape>
        </w:pict>
        XML;

        return preg_replace('/[\r|\n]+/', '', $xml);
    }
}
