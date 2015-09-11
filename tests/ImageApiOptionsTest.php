<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\ImageApiOptions;

class ImageApiOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testWithAllOptions()
    {
        $optionsArr = [
            'image_format'     => ImageApiOptions::FORMAT_JPEG,
            'quality'          => 90,
            'progressive'      => true,
            'width'            => 200,
            'height'           => 250,
            'fit'              => ImageApiOptions::FIT_THUMB,
            'focus'            => ImageApiOptions::FOCUS_FACES,
            'radius'           => 20,
            'background_color' => 'AAAAAA',
        ];
        $options = ImageApiOptions::createFromHumanOptions($optionsArr);
        $this->assertInstanceOf('Markup\Contentful\ImageApiOptions', $options);
        $expected = [
            'bg' => 'rgb:aaaaaa',
            'f' => 'faces',
            'fit' => 'thumb',
            'fl' => 'progressive',
            'fm' => 'jpg',
            'h' => 250,
            'q' => 90,
            'r' => 20,
            'w' => 200,
        ];
        $apiOptionsArray = $options->toArray();
        ksort($apiOptionsArray);
        $this->assertEquals($expected, $apiOptionsArray);
    }

    public function testUsingSetters()
    {
        $options = new ImageApiOptions();
        $options->setImageFormat(ImageApiOptions::FORMAT_JPEG);
        $options->setQuality(90);
        $options->setProgressive(true);
        $options->setWidth(200);
        $options->setHeight(250);
        $options->setFit(ImageApiOptions::FIT_THUMB, ImageApiOptions::FOCUS_FACES);
        $options->setRadius(20);
        $options->setBackgroundColor('AAAAAA');
        $expected = [
            'bg' => 'rgb:aaaaaa',
            'f' => 'faces',
            'fit' => 'thumb',
            'fl' => 'progressive',
            'fm' => 'jpg',
            'h' => 250,
            'q' => 90,
            'r' => 20,
            'w' => 200,
        ];
        $apiOptionsArray = $options->toArray();
        ksort($apiOptionsArray);
        $this->assertEquals($expected, $apiOptionsArray);
    }
}
