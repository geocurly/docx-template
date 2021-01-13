<?php

namespace DocxTemplate\Tests\Processor\Source;

use DocxTemplate\Processor\Source\Image;
use DocxTemplate\Processor\Source\Relation;
use DocxTemplate\Tests\Common\ImageSourceTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DocxTemplate\Processor\Source\Image
 */
class ImageTest extends TestCase
{
    use ImageSourceTrait;

    /**
     * @dataProvider getXmlProvider
     *
     * @param Relation $relation
     * @param string $expected
     * @param string|null $w
     * @param string|null $h
     * @param bool|null $r
     */
    public function testGetXml(Relation $relation, string $expected, ?string $w, ?string $h, ?bool $r = null): void
    {
        $image = new Image($relation, $w, $h, $r);
        self::assertEquals(
            $expected,
            $image->getXml()
        );
    }

    public function testGetExtension(): void
    {
        $rel = [realpath(__DIR__ . '/../../Fixture/Image/cat.jpeg'), 'rId1', 'target', 'type'];
        $image = new Image(
            self::rel(...$rel),
            '150',
            '150',
            false
        );

        self::assertEquals('jpeg', $image->getExtension());
    }

    public function getXmlProvider(): array
    {
        $rel = [realpath(__DIR__ . '/../../Fixture/Image/cat.jpeg'), 'rId1', 'type'];
        // Actual size is 254x198
        $relObj = new Relation(...$rel);
        return [
            [
                $relObj,
                self::imgXml('rId1', '300px', '150px'),
                '300px',
                '150px',
            ],
            [
                $relObj,
                self::imgXml('rId1', '300px', '150px'),
                '300',
                '150',
            ],
            [
                $relObj,
                self::imgXml('rId1', '300px', '150px'),
                '300px',
                '150px',
            ],
            [
                $relObj,
                self::imgXml('rId1', '254px', '198px'),
                null,
                null,
            ],
            [
                $relObj,
                self::imgXml('rId1', '500em', '198px'),
                '500em',
                null,
            ],
            [
                $relObj,
                self::imgXml('rId1', '254px', '500px'),
                null,
                '500px',
            ],
            [
                $relObj,
                self::imgXml('rId1', '254px', '198px'),
                null,
                null,
                true,
            ],
            [
                $relObj,
                self::imgXml('rId1', '192.42px', '150px'),
                null,
                '150px',
                true,
            ],
            [
                $relObj,
                self::imgXml('rId1', '100px', '77.95px'),
                '100px',
                null,
                true,
            ],
            [
                $relObj,
                self::imgXml('rId1', '100px', '77.95px'), // real ratio > given ratio
                '100px',
                '200px',
                true,
            ],
            [
                $relObj,
                self::imgXml('rId1', '128.28px', '100px'), // real ratio < given ratio
                '150px',
                '100px',
                true,
            ],
        ];
    }
}
