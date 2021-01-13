<?php

namespace DocxTemplate\Tests\Processor\Source;

use DocxTemplate\Processor\Source\Image;
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
     * @param string $expected
     * @param string $id
     * @param string $url
     * @param string|null $w
     * @param string|null $h
     * @param bool|null $r
     */
    public function testGetXml(string $expected, ?string $w, ?string $h, ?bool $r = null): void
    {
        $image = new Image('rId1', realpath(__DIR__ . '/../../Fixture/Image/cat.jpeg') , $w, $h, $r);
        self::assertEquals(
            $expected,
            $image->getXml()
        );
    }

    public function testGetExtension(): void
    {
        $image = new Image(
            'rId1',
            realpath(__DIR__ . '/../../Fixture/Image/cat.jpeg'),
            '150',
            '150',
            false
        );

        self::assertEquals('jpeg', $image->getExtension());
    }

    public function getXmlProvider(): array
    {
        // Actual size is 254x198
        return [
            [
                self::imgXml('rId1', '300px', '150px'),
                '300px',
                '150px',
            ],
            [
                self::imgXml('rId1', '300px', '150px'),
                '300',
                '150',
            ],
            [
                self::imgXml('rId1', '300px', '150px'),
                '300px',
                '150px',
            ],
            [
                self::imgXml('rId1', '254px', '198px'),
                null,
                null,
            ],
            [
                self::imgXml('rId1', '500em', '198px'),
                '500em',
                null,
            ],
            [
                self::imgXml('rId1', '254px', '500px'),
                null,
                '500px',
            ],
            [
                self::imgXml('rId1', '254px', '198px'),
                null,
                null,
                true,
            ],
            [
                self::imgXml('rId1', '192.42px', '150px'),
                null,
                '150px',
                true,
            ],
            [
                self::imgXml('rId1', '100px', '77.95px'),
                '100px',
                null,
                true,
            ],
            [
                self::imgXml('rId1', '100px', '77.95px'), // real ratio > given ratio
                '100px',
                '200px',
                true,
            ],
            [
                self::imgXml('rId1', '128.28px', '100px'), // real ratio < given ratio
                '150px',
                '100px',
                true,
            ],
        ];
    }
}
