<?php

namespace Markup\Contentful\Tests;

use Markup\Contentful\ContentType;
use Markup\Contentful\ContentTypeField;
use Markup\Contentful\ContentTypeInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ContentTypeTest extends MockeryTestCase
{
    public function testIsContentType()
    {
        $refl = new \ReflectionClass(ContentType::class);
        $this->assertTrue($refl->implementsInterface(ContentTypeInterface::class));
    }

    public function testGetDisplayFieldWhenDefined()
    {
        $field1 = m::mock(ContentTypeField::class);
        $field2 = m::mock(ContentTypeField::class);
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
