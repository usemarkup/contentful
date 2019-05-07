<?php

namespace Markup\Contentful\Tests;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\AssetInterface;
use Markup\Contentful\Contentful;
use Markup\Contentful\ContentTypeInterface;
use Markup\Contentful\Decorator\AssetDecoratorInterface;
use Markup\Contentful\EntryInterface;
use Markup\Contentful\Exception\InvalidIdException;
use Markup\Contentful\Exception\ResourceUnavailableException;
use Markup\Contentful\Filter\BeforeFilter;
use Markup\Contentful\Filter\EqualFilter;
use Markup\Contentful\Filter\LessThanFilter;
use Markup\Contentful\Link;
use Markup\Contentful\Log\LogInterface;
use Markup\Contentful\Metadata;
use Markup\Contentful\Promise\ResourceArrayPromise;
use Markup\Contentful\Property\FieldProperty;
use Markup\Contentful\Property\SystemProperty;
use Markup\Contentful\ResourceArray;
use Markup\Contentful\ResourceArrayInterface;
use Markup\Contentful\SpaceInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ContentfulTest extends MockeryTestCase
{
    /**
     * @var array
     */
    private $spaces;

    /**
     * @var array
     */
    private $options;

    protected function setUp()
    {
        $this->spaces = [
            'test' => [
                'key' => 'jskdfjhsdfk',
                'access_token' => '235345lj34h53j4h',
            ],
            'test1' => [
                'key' => 'sldjhfsdjh',
                'access_token' => 'lkj45k3jh453kj5h',
                'api_domain' => 'different.domain.com',
            ],
        ];
        $this->options = [
            'dynamic_entries' => false,
        ];
    }

    public function testGetSpace()
    {
        $data = $this->getSpaceData();
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $space = $contentful->getSpace('test');
        $this->assertInstanceOf(SpaceInterface::class, $space);
        $this->assertEquals('Contentful Example API', $space->getName());
    }

    public function testGetSpaceAsync()
    {
        $data = $this->getSpaceData();
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $space = $contentful->getSpaceAsync('test');
        $this->assertInstanceOf(PromiseInterface::class, $space);
        $this->assertInstanceOf(SpaceInterface::class, $space);
        $this->assertEquals('Contentful Example API', $space->getName());
    }

    private function getSpaceData()
    {
        return [
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
    }

    public function testGetEntry()
    {
        $data = $this->getEntryData();
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $entry = $contentful->getEntry('cat', 'test');
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertEquals(['rainbows', 'fish'], $entry->getFields()['likes']);
    }

    public function testGetEntryAsync()
    {
        $data = $this->getEntryData();
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $entry = $contentful->getEntryAsync('cat', 'test');
        $this->assertInstanceOf(PromiseInterface::class, $entry);
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertEquals(['rainbows', 'fish'], $entry->getFields()['likes']);
    }

    public function testGetEntryAsyncDoesNotBlowUpWhenDelayedFetchExplodes()
    {
        $handlerOption = $this->getExplodyHandlerOption();
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $entry = $contentful->getEntryAsync('cat', 'test');
        $this->assertInstanceOf(PromiseInterface::class, $entry);
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertEquals('', $entry->getField('field'));
    }

    private function getEntryData()
    {
        return [
            'sys' => [
                'type' => 'Entry',
                'id' => 'cat',
                'space' => [
                    'sys' => [
                        'type' => 'Link',
                        'linkType' => 'Space',
                        'id' => 'example',
                    ],
                ],
                'createdAt' => '2013-03-26T00:13:37.123Z',
                'updatedAt' => '2013-03-26T00:13:37.123Z',
                'revision' => 1,
            ],
            'fields' => [
                'name' => 'Nyan cat',
                'color' => 'Rainbow',
                'nyan' => true,
                'birthday' => '2011-04-02T00:00:00.000Z',
                'diary' => 'Nyan cat has an epic rainbow trail.',
                'likes' => ['rainbows', 'fish'],
                'bestFriend' => [
                    'sys' => [
                        'type' => 'Link',
                        'linkType' => 'Entry',
                        'id' => 'happycat',
                    ],
                ],
            ]
        ];
    }

    public function testGetAsset()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getSuccessAssetData(), '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $asset = $contentful->getAsset('nyancat', 'test');
        $this->assertInstanceOf(AssetInterface::class, $asset);
        $this->assertEquals(1, $asset->getRevision());
    }

    public function testGetAssetAsync()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getSuccessAssetData(), '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $asset = $contentful->getAssetAsync('nyancat', 'test');
        $this->assertInstanceOf(PromiseInterface::class, $asset);
        $this->assertInstanceOf(AssetInterface::class, $asset);
        $this->assertEquals(1, $asset->getRevision());
    }

    public function testGetAssetDecorates()
    {
        $assetDecorator = m::mock(AssetDecoratorInterface::class);
        $decoratedAsset = m::mock(AssetInterface::class)->shouldIgnoreMissing();
        $assetDecorator
            ->shouldReceive('decorate')
            ->with(m::type(AssetInterface::class))
            ->andReturn($decoratedAsset);
        $spaces = array_merge_recursive($this->spaces, ['test' => ['asset_decorator' => $assetDecorator]]);
        $handlerOption = $this->getSuccessHandlerOption($this->getSuccessAssetData(), '235345lj34h53j4h');
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption));
        $asset = $contentful->getAsset('nyancat', 'test');
        $this->assertSame($asset, $decoratedAsset);
    }

    public function testGetAssets()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getSuccessAssetData(), '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $assets = $contentful->getAssets([], 'test');
        $this->assertInstanceOf(AssetInterface::class, $assets);
    }

    public function testGetAssetsAsync()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getSuccessAssetData(), '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $assets = $contentful->getAssetsAsync([], 'test');
        $this->assertInstanceOf(ResourceArrayPromise::class, $assets);
        $this->assertInstanceOf(PromiseInterface::class, $assets);
    }

    public function testIsAssetUnlinked()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getEntriesData(), '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $assetUnlinked = $contentful->isAssetUnlinked('nyancat', 'test');
        $this->assertIsBool($assetUnlinked);
        $this->assertFalse($assetUnlinked);
    }

    private function getSuccessAssetData()
    {
        return [
            'sys' => [
                'type' => 'Asset',
                'id' => 'nyancat',
                'space' => [
                    'sys' => [
                        'type' => 'Link',
                        'linkType' => 'Space',
                        'id' => 'example',
                    ],
                ],
                'createdAt' => '2013-03-26T00:13:37.123Z',
                'updatedAt' => '2013-03-26T00:13:37.123Z',
                'revision' => 1,
            ],
            'fields' => [
                'title' => 'Nyan cat',
                'description' => 'A typical picture of Nyancat including the famous rainbow trail.',
                'file' => [
                    'fileName' => 'nyancat.png',
                    'contentType' => 'image/png',
                    'details' => [
                        'image' => [
                            'width' => 250,
                            'height' => 250,
                        ],
                        'size' => 12273,
                    ],
                    'url' => '//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png',
                ],
            ],
        ];
    }

    public function testGetContentType()
    {
        $data = $this->getContentTypesData();
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $contentType = $contentful->getContentType('cat', 'test');
        $this->assertInstanceOf(ContentTypeInterface::class, $contentType);
        $this->assertEquals('Meow.', $contentType->getDescription());
    }

    public function testGetContentTypeAsync()
    {
        $data = $this->getContentTypesData();
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $contentType = $contentful->getContentTypeAsync('cat', 'test');
        $this->assertInstanceOf(PromiseInterface::class, $contentType);
        $this->assertInstanceOf(ContentTypeInterface::class, $contentType);
        $this->assertEquals('Meow.', $contentType->getDescription());
    }

    public function testGetEntries()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getEntriesData(), '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $entries = $contentful->getEntries([new EqualFilter(new SystemProperty('id'), 'nyancat')], 'test');
        $this->assertInstanceOf(ResourceArrayInterface::class, $entries);
        $this->assertCount(1, $entries);
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertInstanceOf(EntryInterface::class, $entry['bestFriend']);
    }

    public function testGetEntriesAsync()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getEntriesData(), '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $entries = $contentful->getEntriesAsync([new EqualFilter(new SystemProperty('id'), 'nyancat')], 'test');
        $this->assertInstanceOf(PromiseInterface::class, $entries);
        $this->assertInstanceOf(ResourceArrayInterface::class, $entries);
        $this->assertCount(1, $entries);
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertInstanceOf(EntryInterface::class, $entry['bestFriend']);
    }

    public function testCacheMissDoesFetch()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getEntriesData(), '235345lj34h53j4h');
        $expectedCacheKey = 'jskdfjhsdfk-entries-|equal|fields.old↦6,|less_than|fields.ghosts[lt]↦6';
        $cachePool = $this->getMockCachePool();
        $cacheItem = $this->getMockCacheItem();
        $cachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($cacheItem);
        $cacheItem
            ->shouldReceive('isHit')
            ->once()
            ->andReturn(false);
        $cacheItem
            ->shouldReceive('set')
            ->once();
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption));
        $filters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $contentful->getEntries($filters, 'test');
    }

    public function testBeforeFilterUsesOwnCacheKey()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getEntriesData(), '235345lj34h53j4h');
        $expectedCacheKey = 'jskdfjhsdfk-entries-|before|fields.published↦now';
        $cachePool = $this->getMockCachePool();
        $cacheItem = $this->getMockCacheItem();
        $cachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($cacheItem);
        $cacheItem
            ->shouldReceive('isHit')
            ->once()
            ->andReturn(false);
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption));
        $filters = [new BeforeFilter(new FieldProperty('published'), 'now')];
        $contentful->getEntries($filters, 'test');
    }

    public function testCacheHitUsesCacheAndDoesNotFetch()
    {
        $handlerOption = $this->getExplodyHandlerOption();
        $expectedCacheKey = 'jskdfjhsdfk-entries-|equal|fields.old↦6,|less_than|fields.ghosts[lt]↦6';
        $cachePool = $this->getMockCachePool();
        $cacheItem = $this->getMockCacheItem();
        $cachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($cacheItem);
        $cacheItem
            ->shouldReceive('isHit')
            ->once()
            ->andReturn(true);
        $cacheItem
            ->shouldReceive('get')
            ->andReturn(json_encode($this->getEntriesData()));
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption));
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $entries = $contentful->getEntries($parameters, 'test');
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertInstanceOf(EntryInterface::class, $entry['bestFriend']);
    }

    public function testUsesFallbackCacheOnRequestFailure()
    {
        $handlerOption = $this->getExplodyHandlerOption();
        $expectedCacheKey = 'jskdfjhsdfk-entries-|equal|fields.old↦6,|less_than|fields.ghosts[lt]↦6';
        $frontCacheItem = $this->getMockCacheItem();
        $frontCachePool = $this->getMockCachePool();
        $frontCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($frontCacheItem);
        $frontCacheItem
            ->shouldReceive('isHit')
            ->andReturn(false);
        $fallbackCacheItem = $this->getMockCacheItem();
        $fallbackCachePool = $this->getMockCachePool();
        $fallbackCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($fallbackCacheItem);
        $fallbackCacheItem
            ->shouldReceive('isHit')
            ->andReturn(true);
        $fallbackCacheItem
            ->shouldReceive('get')
            ->andReturn(json_encode($this->getEntriesData()));
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $frontCachePool, 'fallback_cache' => $fallbackCachePool]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption));
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $entries = $contentful->getEntries($parameters, 'test');
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertInstanceOf(EntryInterface::class, $entry['bestFriend']);
    }

    public function testThrowsResourceUnavailableExceptionIfFailResponseCachedInFallbackCache()
    {
        $handlerOption = $this->getExplodyHandlerOption();
        $expectedCacheKey = 'jskdfjhsdfk-entries-|equal|fields.old↦6,|less_than|fields.ghosts[lt]↦6';
        $frontCacheItem = $this->getMockCacheItem();
        $frontCachePool = $this->getMockCachePool();
        $frontCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($frontCacheItem);
        $frontCacheItem
            ->shouldReceive('isHit')
            ->andReturn(false);
        $fallbackCacheItem = $this->getMockCacheItem();
        $fallbackCachePool = $this->getMockCachePool();
        $fallbackCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($fallbackCacheItem);
        $fallbackCacheItem
            ->shouldReceive('isHit')
            ->andReturn(true);
        $fallbackCacheItem
            ->shouldReceive('get')
            ->andReturn(json_encode(null));
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $frontCachePool, 'fallback_cache' => $fallbackCachePool]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption));
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $this->expectException(ResourceUnavailableException::class);
        $this->expectExceptionMessage('The entries from the space "test" were unavailable.');
        $contentful->getEntries($parameters, 'test');
    }

    public function testFailResponseDoesNotSaveIntoFallbackCacheEvenIfCachingFailResponses()
    {
        $handlerOption = $this->getExplodyHandlerOption();
        $expectedCacheKey = 'jskdfjhsdfk-entries-|equal|fields.old↦6,|less_than|fields.ghosts[lt]↦6';
        $frontCacheItem = $this->getMockCacheItem();
        $frontCachePool = $this->getMockCachePool();
        $frontCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($frontCacheItem);
        $fallbackCacheItem = $this->getMockCacheItem();
        $fallbackCachePool = $this->getMockCachePool();
        $fallbackCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($fallbackCacheItem);
        $fallbackCacheItem
            ->shouldReceive('set')
            ->never();
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $frontCachePool, 'fallback_cache' => $fallbackCachePool]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption, ['cache_fail_responses' => true]));
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $this->expectException(ResourceUnavailableException::class);
        $this->expectExceptionMessage('The entries from the space "test" were unavailable.');
        $contentful->getEntries($parameters, 'test');
    }

    public function testUsesFallbackCacheOnRequestSuccessfulButInvalid()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getEmptyEntriesData(), '235345lj34h53j4h');
        $expectedCacheKey = 'jskdfjhsdfk-entries-|equal|fields.old↦6,|less_than|fields.ghosts[lt]↦6';
        $frontCacheItem = $this->getMockCacheItem();
        $frontCachePool = $this->getMockCachePool();
        $frontCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($frontCacheItem);
        $frontCacheItem
            ->shouldReceive('isHit')
            ->andReturn(false);
        $fallbackCacheItem = $this->getMockCacheItem();
        $fallbackCachePool = $this->getMockCachePool();
        $fallbackCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($fallbackCacheItem);
        $fallbackCacheItem
            ->shouldReceive('isHit')
            ->andReturn(true);
        $fallbackCacheItem
            ->shouldReceive('get')
            ->andReturn(json_encode($this->getEntriesData()));
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $frontCachePool, 'fallback_cache' => $fallbackCachePool]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption));
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $entries = $contentful->getEntries($parameters, 'test', [
            'test' => function ($builtResponse) {
                if (!$builtResponse instanceof ResourceArray) {
                    return false;
                }

                return count($builtResponse) === 1;
            }
        ]);
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertInstanceOf(EntryInterface::class, $entry['bestFriend']);
    }

    public function testInvalidResponseDoesNotSaveIntoFallbackCacheEvenIfCachingFailResponses()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getEmptyEntriesData(), '235345lj34h53j4h');
        $expectedCacheKey = 'jskdfjhsdfk-entries-|equal|fields.old↦6,|less_than|fields.ghosts[lt]↦6';
        $frontCacheItem = $this->getMockCacheItem();
        $frontCachePool = $this->getMockCachePool();
        $frontCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($frontCacheItem);
        $fallbackCacheItem = $this->getMockCacheItem();
        $fallbackCachePool = $this->getMockCachePool();
        $fallbackCachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($fallbackCacheItem);
        $fallbackCacheItem
            ->shouldReceive('set')
            ->never();
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $frontCachePool, 'fallback_cache' => $fallbackCachePool]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption, ['cache_fail_responses' => true]));
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $this->expectException(ResourceUnavailableException::class);
        $this->expectExceptionMessage('Contentful returned a valid response but it did not pass the provided test');
        $contentful->getEntries($parameters, 'test', [
            'test' => function ($builtResponse) {
                if (!$builtResponse instanceof ResourceArray) {
                    return false;
                }

                return count($builtResponse) === 1;
            }
        ]);
    }

    public function testFlushCache()
    {
        $cachePool = $this->getMockCachePool();
        $cachePool
            ->shouldReceive('clear')
            ->once();
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool]]);
        $contentful = $this->getContentful($spaces);
        $contentful->flushCache('test');
    }

    public function testResolveContentTypeLink()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getContentTypesData(), '235345lj34h53j4h');
        $data = [
            'type' => 'Link',
            'linkType' => 'ContentType',
            'id' => 'cat',
        ];
        $metadata = new Metadata();
        $metadata->setType($data['type']);
        $metadata->setLinkType($data['linkType']);
        $metadata->setId($data['id']);
        $link = new Link($metadata, 'test');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $contentType = $contentful->resolveLink($link)->wait();
        $this->assertInstanceOf(ContentTypeInterface::class, $contentType);
        $this->assertEquals('cat', $contentType->getId());//of course, in a real situation this would be the same as the ID in the link - but this is the ID in the mock data
        $this->assertEquals('Name', $contentType->getDisplayField()->getName());
    }

    public function testQueryIsLoggedIfLoggerTrue()
    {
        $handlerOption = $this->getSuccessHandlerOption($this->getEntriesData(), '235345lj34h53j4h');
        $contentful = $this->getContentful($this->spaces, array_merge($this->options, ['logger' => true], $handlerOption));
        $contentful->getEntries([new EqualFilter(new SystemProperty('id'), 'nyancat')], 'test');
        $logs = $contentful->getLogs();
        $this->assertCount(1, $logs);
        $this->assertContainsOnlyInstancesOf(LogInterface::class, $logs);
        $log = reset($logs);
        $this->assertEquals(LogInterface::RESOURCE_ENTRY, $log->getResourceType());
    }

    public function testUsePreviewApiForCachedGetEntriesCall()
    {
        $handlerOption = $this->getExplodyHandlerOption();
        $expectedCacheKey = 'jskdfjhsdfk-entries-preview-|equal|fields.old↦6,|less_than|fields.ghosts[lt]↦6';
        $cachePool = $this->getMockCachePool();
        $cacheItem = $this->getMockCacheItem();
        $cachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($cacheItem);
        $cacheItem
            ->shouldReceive('isHit')
            ->once()
            ->andReturn(true);
        $cacheItem
            ->shouldReceive('get')
            ->andReturn(json_encode($this->getEntriesData()));
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool, 'preview_mode' => true]]);
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption));
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $entries = $contentful->getEntries($parameters, 'test');
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf(EntryInterface::class, $entry);
        $this->assertInstanceOf(EntryInterface::class, $entry['bestFriend']);
    }

    public function testGetContentTypes()
    {
        $data = [$this->getContentTypeData()];
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $contentTypes = $contentful->getContentTypes([], 'test');
        $this->assertCount(1, $contentTypes);
        $this->assertContainsOnlyInstancesOf(ContentTypeInterface::class, $contentTypes);
    }

    public function testGetContentTypesAsync()
    {
        $data = [$this->getContentTypeData()];
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $contentTypes = $contentful->getContentTypesAsync([], 'test');
        $this->assertInstanceOf(PromiseInterface::class, $contentTypes);
        $this->assertCount(1, $contentTypes);
        $this->assertContainsOnlyInstancesOf(ContentTypeInterface::class, $contentTypes);
    }

    public function testGetContentTypeByNameWhenExists()
    {
        $data = [$this->getContentTypeData()];
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $name = 'Cat';
        $contentType = $contentful->getContentTypeByName($name, 'test');
        $this->assertInstanceOf(ContentTypeInterface::class, $contentType);
        $this->assertEquals($name, $contentType->getName());
    }

    public function testGetContentTypeByNameWhenDoesNotExist()
    {
        $data = [$this->getContentTypeData()];
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $this->assertNull($contentful->getContentTypeByName('Dog', 'test'));
    }

    public function testGetContentTypeByNameAsync()
    {
        $data = [$this->getContentTypeData()];
        $handlerOption = $this->getSuccessHandlerOption($data, '235345lj34h53j4h');
        $contentful = $this->getContentful(null, array_merge($this->options, $handlerOption));
        $name = 'Cat';
        $contentType = $contentful->getContentTypeByNameAsync($name, 'test');
        $this->assertInstanceOf(PromiseInterface::class, $contentType);
        $this->assertInstanceOf(ContentTypeInterface::class, $contentType);
        $this->assertEquals($name, $contentType->getName());
    }

    public function testGetNonExistentResourceWhenCachedThrowsWithoutRequest()
    {
        $expectedCacheKey = 'jskdfjhsdfk-entry↦cat';
        $cachePool = $this->getMockCachePool();
        $cacheItem = $this->getMockCacheItem();
        $cachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->andReturn($cacheItem);
        $cacheItem
            ->shouldReceive('isHit')
            ->once()
            ->andReturn(true);
        $cacheItem
            ->shouldReceive('get')
            ->once()
            ->andReturn(json_encode(null));
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool, 'preview_mode' => false]]);
        $handlerOption = $this->getExplodyHandlerOption();
        $contentful = $this->getContentful($spaces, array_merge($this->options, $handlerOption, ['cache_fail_responses' => true]));
        $this->expectException(ResourceUnavailableException::class);
        $this->expectExceptionMessage('Fetched fail response from cache for key "jskdfjhsdfk-entry↦cat".');
        $contentful->getEntry('cat', 'test');
    }

    public function testUseInvalidIdWithEntry()
    {
        $this->expectException(InvalidIdException::class);
        $spaces = array_merge_recursive($this->spaces);
        $contentful = $this->getContentful($spaces);
        $contentful->getEntry('🦈', 'test');
    }

    public function testUseInvalidIdWithAsset()
    {
        $this->expectException(InvalidIdException::class);
        $spaces = array_merge_recursive($this->spaces);
        $contentful = $this->getContentful($spaces);
        $contentful->getAsset('🎣', 'test');
    }

    public function testUseInvalidIdWithContentType()
    {
        $this->expectException(InvalidIdException::class);
        $spaces = array_merge_recursive($this->spaces);
        $contentful = $this->getContentful($spaces);
        $contentful->getContentType('🐠', 'test');
    }

    private function getSuccessHandlerOption($data, $accessToken)
    {
        $handler = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], \GuzzleHttp\Psr7\stream_for(json_encode($data))),
        ]);

        return ['guzzle_handler' => HandlerStack::create($handler)];
    }

    private function getExplodyHandlerOption()
    {
        $handler = new \GuzzleHttp\Handler\MockHandler([
            function ($request) {
                throw new ConnectException('exploded!', $request);
            }
        ]);

        return ['guzzle_handler' => HandlerStack::create($handler)];
    }

    private function getMockCachePool()
    {
        return m::mock(CacheItemPoolInterface::class)->shouldIgnoreMissing();
    }

    private function getMockCacheItem()
    {
        return m::mock(CacheItemInterface::class)->shouldIgnoreMissing();
    }

    private function getEntriesData()
    {
        return [
            'sys' => [
                'type' => 'Array',
            ],
            'total' => 1,
            'skip' => 0,
            'limit' => 100,
            'items' => [
                [
                    'fields' => [
                        'name' => 'Happy Cat',
                        'bestFriend' => [
                            'sys' => [
                                'type' => 'Link',
                                'linkType' => 'Entry',
                                'id' => 'nyancat',
                            ],
                        ],
                        'likes' => [
                            'cheezburger',
                        ],
                        'color' => 'gray',
                        'birthday' => '2003-10-28T23:00:00+00:00',
                        'lives' => 1,
                        'image' => [
                            'sys' => [
                                'type' => 'Link',
                                'linkType' => 'Asset',
                                'id' => 'happycat',
                            ],
                        ],
                    ],
                    'sys' => [
                        'space' => [
                            'sys' => [
                                'type' => 'Link',
                                'linkType' => 'Space',
                                'id' => 'cfexampleapi',
                            ],
                        ],
                        'type' => 'Entry',
                        'contentType' => [
                            'sys' => [
                                'type' => 'Link',
                                'linkType' => 'ContentType',
                                'id' => 'cat',
                            ],
                        ],
                        'id' => 'happycat',
                        'revision' => 8,
                        'createdAt' => '2013-06-27T22:46:20.171Z',
                        'updatedAt' => '2013-11-18T15:58:02.018Z',
                        'locale' => 'en-US',
                    ],
                ],
            ],
            'includes' => [
                'Entry' => [
                    [
                        'fields' => [
                            'name' => 'Nyan Cat',
                            'likes' => [
                                'rainbows',
                                'fish',
                            ],
                            'color' => 'rainbow',
                            'bestFriend' => [
                                'sys' => [
                                    'type' => 'Link',
                                    'linkType' => 'Entry',
                                    'id' => 'happycat',
                                ],
                            ],
                            'birthday' => '2011-04-04T22:00:00+00:00',
                            'lives' => 1337,
                            'image' => [
                                'sys' => [
                                    'type' => 'Link',
                                    'linkType' => 'Asset',
                                    'id' => 'nyancat',
                                ],
                            ],
                        ],
                        'sys' => [
                            'space' => [
                                'sys' => [
                                    'type' => 'Link',
                                    'linkType' => 'Space',
                                    'id' => 'cfexampleapi',
                                ],
                            ],
                            'type' => 'Entry',
                            'contentType' => [
                                'sys' => [
                                    'type' => 'Link',
                                    'linkType' => 'ContentType',
                                    'id' => 'cat',
                                ],
                            ],
                            'id' => 'nyancat',
                            'revision' => 5,
                            'createdAt' => '2013-06-27T22:46:19.513Z',
                            'updatedAt' => '2013-09-04T09:19:39.027Z',
                            'locale' => 'en-US',
                        ],
                    ],
                ],
                'Asset' => [
                    [
                        'fields' => [
                            'file' => [
                                'fileName' => 'happycatw.jpg',
                                'contentType' => 'image/jpeg',
                                'details' => [
                                    'image' => [
                                        'width' => 273,
                                        'height' => 397,
                                    ],
                                    'size' => 59939,
                                ],
                                'url' => '//images.contentful.com/cfexampleapi/3MZPnjZTIskAIIkuuosCss/382a48dfa2cb16c47aa2c72f7b23bf09/happycatw.jpg',
                            ],
                            'title' => 'Happy Cat',
                        ],
                        'sys' => [
                            'space' => [
                                'sys' => [
                                    'type' => 'Link',
                                    'linkType' => 'Space',
                                    'id' => 'cfexampleapi',
                                ],
                            ],
                            'type' => 'Asset',
                            'id' => 'happycat',
                            'revision' => 2,
                            'createdAt' => '2013-09-02T14:56:34.267Z',
                            'updatedAt' => '2013-09-02T15:11:24.361Z',
                            'locale' => 'en-US',
                        ],
                    ],
                    [
                        'fields' => [
                            'title' => 'Nyan Cat',
                            'file' => [
                                'fileName' => 'Nyan_cat_250px_frame.png',
                                'contentType' => 'image/png',
                                'details' => [
                                    'image' => [
                                        'width' => 250,
                                        'height' => 250,
                                    ],
                                    'size' => 12273,
                                ],
                                'url' => '//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png',
                            ],
                        ],
                        'sys' => [
                            'space' => [
                                'sys' => [
                                    'type' => 'Link',
                                    'linkType' => 'Space',
                                    'id' => 'cfexampleapi',
                                ],
                            ],
                            'type' => 'Asset',
                            'id' => 'nyancat',
                            'revision' => 1,
                            'createdAt' => '2013-09-02T14:56:34.240Z',
                            'updatedAt' => '2013-09-02T14:56:34.240Z',
                            'locale' => 'en-US',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getEmptyEntriesData()
    {
        return [
            'sys' => [
                'type' => 'Array',
            ],
            'total' => 0,
            'skip' => 0,
            'limit' => 100,
            'items' => [],
            'includes' => [],
        ];
    }

    private function getContentTypeData()
    {
        return [
            'sys' => [
                'type' => 'ContentType',
                'id' => 'cat',
            ],
            'name' => 'Cat',
            'description' => 'Meow.',
            'fields' => [
                [
                    'id' => 'name',
                    'name' => 'Name',
                    'type' => 'Text',
                ],
                [
                    'id' => 'diary',
                    'name' => 'Diary',
                    'type' => 'Text',
                ],
                [
                    'id' => 'likes',
                    'name' => 'Likes',
                    'type' => 'Array',
                    'items' => [
                        'type' => 'Symbol',
                    ]
                ],
                [
                    'id' => 'bestFriend',
                    'name' => 'Best Friend',
                    'type' => 'Link',
                ],
                [
                    'id' => 'lifes',
                    'name' => 'Lifes',
                    'type' => 'Integer',
                ],
            ],
            'displayField' => 'name',
        ];
    }

    private function getContentTypesData()
    {
        return [
            'sys' => [
                'type' => 'Array',
            ],
            'total' => 1,
            'skip' => 0,
            'limit' => 100,
            'items' => [
                [
                    'sys' => [
                        'type' => 'ContentType',
                        'id' => 'cat',
                    ],
                    'name' => 'Cat',
                    'description' => 'Meow.',
                    'fields' => [
                        [
                            'id' => 'name',
                            'name' => 'Name',
                            'type' => 'Text',
                        ],
                        [
                            'id' => 'diary',
                            'name' => 'Diary',
                            'type' => 'Text',
                        ],
                        [
                            'id' => 'likes',
                            'name' => 'Likes',
                            'type' => 'Array',
                            'items' => [
                                'type' => 'Symbol',
                            ]
                        ],
                        [
                            'id' => 'bestFriend',
                            'name' => 'Best Friend',
                            'type' => 'Link',
                        ],
                        [
                            'id' => 'lifes',
                            'name' => 'Lifes',
                            'type' => 'Integer',
                        ],
                    ],
                    'displayField' => 'name',
                ],
            ],
        ];
    }

    private function getContentful($spaces = null, $options = null)
    {
        return new Contentful(
            $spaces ?? $this->spaces,
            $options ?? $this->options
        );
    }
}
