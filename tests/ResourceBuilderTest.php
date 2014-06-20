<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\ResourceBuilder;

class ResourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->builder = new ResourceBuilder();
    }

    public function testBuildSpace()
    {
        $data = [
            'sys' => [
                'type' => 'Space',
                'id' => 'cfexampleapi',
            ],
            'name' => 'Contentful Example API',
            'locales' => [
                [
                    'code' => 'en-US',
                    'name' => 'English',
                ],
                [
                    'code' => 'tlh',
                    'name' => 'Klingon',
                ],
            ],
        ];
        $space = $this->builder->buildFromData($data);
        $this->assertInstanceOf('Markup\Contentful\SpaceInterface', $space);
        $this->assertEquals('cfexampleapi', $space->getId());
        $locale = $space->getLocales()[1];
        $this->assertInstanceOf('Markup\Contentful\Locale', $locale);
        $this->assertEquals('Klingon', $locale->getName());
    }
}
