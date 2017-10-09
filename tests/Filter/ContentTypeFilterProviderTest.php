<?php

namespace Markup\Contentful\Tests\Filter;

use Markup\Contentful\Contentful;
use Markup\Contentful\ContentTypeInterface;
use Markup\Contentful\Filter\ContentTypeFilter;
use Markup\Contentful\Filter\ContentTypeFilterProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ContentTypeFilterProviderTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->contentful = m::mock(Contentful::class);
        $this->provider = new ContentTypeFilterProvider($this->contentful);
    }

    public function testCreateForExistingContentType()
    {
        $contentType = m::mock(ContentTypeInterface::class);
        $id = 42;
        $contentType
            ->shouldReceive('getId')
            ->andReturn($id);
        $name = 'unique_type';
        $contentType
            ->shouldReceive('getName')
            ->andReturn($name);
        $this->contentful
            ->shouldReceive('getContentTypeByName')
            ->with($name, m::any())
            ->andReturn($contentType);
        $filter = $this->provider->createForContentTypeName($name);
        $this->assertInstanceOf(ContentTypeFilter::class, $filter);
        $this->assertEquals($id, $filter->getValue());
    }

    public function testCreateForNotExistingContentType()
    {
        $this->contentful
            ->shouldReceive('getContentTypeByName')
            ->andReturn(null);
        $this->assertNull($this->provider->createForContentTypeName('unknown'));
    }
}
