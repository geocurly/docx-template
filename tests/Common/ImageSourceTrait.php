<?php

declare(strict_types=1);

namespace DocxTemplate\Tests\Common;

trait ImageSourceTrait
{
    protected static function imgXml(string $id, string $w, string $h): string
    {
        $xml = <<<XML
        <w:pict>
           <v:shape type="#_x0000_t75" style="width:{$w};height:{$h}">
                <v:imagedata r:id="{$id}" o:title=""/>
            </v:shape>
        </w:pict>
        XML;

        return preg_replace('/>\s+</', '><', $xml);
    }
}
