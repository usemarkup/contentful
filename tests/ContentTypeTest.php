<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\ContentType;
use Mockery as m;

class ContentTypeTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testIsContentType()
    {
        $refl = new \ReflectionClass('Markup\Contentful\ContentType');
        $this->assertTrue($refl->implementsInterface('Markup\Contentful\ContentTypeInterface'));
    }

    public function testGetDisplayFieldWhenDefined()
    {
        $field1 = m::mock('Markup\Contentful\ContentTypeField');
        $field2 = m::mock('Markup\Contentful\ContentTypeField');
        $id1 = 'id1';
        $id2 = 'id2';
        $field1
            ->shouldReceive('getId')
            ->andReturn($id1);
        $field2
            ->shouldReceive('getId')
            ->andReturn($id2);
        $contentType = new ContentType('name', 'description', [$field1, $field2], m::mock('Markup\Contentful\MetadataInterface'), $id2);
        $this->assertSame($field2, $contentType->getDisplayField());
    }
}
