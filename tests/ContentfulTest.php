<?php

namespace Markup\Contentful\Tests;

use GuzzleHttp\Adapter\MockAdapter;
use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Markup\Contentful\Contentful;
use Markup\Contentful\Filter\EqualFilter;
use Markup\Contentful\Filter\LessThanFilter;
use Markup\Contentful\Link;
use Markup\Contentful\Log\LogInterface;
use Markup\Contentful\Metadata;
use Markup\Contentful\Property\FieldProperty;
use Markup\Contentful\Property\SystemProperty;
use Mockery as m;

class ContentfulTest extends \PHPUnit_Framework_TestCase
{
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
        $this->mockAdapter = new MockAdapter();
        $this->options = [
            'guzzle_adapter' => $this->mockAdapter,
            'dynamic_entries' => false,
        ];
        $this->contentful = new Contentful($this->spaces, $this->options);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testGetSpace()
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
        $response = $this->getSuccessMockResponse($data, '235345lj34h53j4h');
        $this->mockAdapter->setResponse($response);
        $space = $this->contentful->getSpace();
        $this->assertInstanceOf('Markup\Contentful\SpaceInterface', $space);
        $this->assertEquals('Contentful Example API', $space->getName());
    }

    public function testGetEntry()
    {
        $data = [
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
        $response = $this->getSuccessMockResponse($data, '235345lj34h53j4h');
        $this->mockAdapter->setResponse($response);
        $entry = $this->contentful->getEntry('cat');
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $entry);
        $this->assertEquals(['rainbows', 'fish'], $entry->getFields()['likes']);
    }

    public function testGetAsset()
    {
        $data = [
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
        $response = $this->getSuccessMockResponse($data, '235345lj34h53j4h');
        $this->mockAdapter->setResponse($response);
        $asset = $this->contentful->getAsset('nyancat');
        $this->assertInstanceOf('Markup\Contentful\AssetInterface', $asset);
        $this->assertEquals(1, $asset->getRevision());
    }

    public function testGetContentType()
    {
        $data = [
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
                    'localized' => true,
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
                    ],
                ],
                [
                    'id' => 'lifes',
                    'name' => 'Lifes left',
                    'type' => 'Integer',
                ],
            ],
        ];
        $response = $this->getSuccessMockResponse($data, '235345lj34h53j4h');
        $this->mockAdapter->setResponse($response);
        $contentType = $this->contentful->getContentType('cat');
        $this->assertInstanceOf('Markup\Contentful\ContentTypeInterface', $contentType);
        $this->assertEquals('Meow.', $contentType->getDescription());
    }

    public function testGetEntries()
    {
        $response = $this->getSuccessMockResponse($this->getEntriesData(), '235345lj34h53j4h');
        $this->mockAdapter->setResponse($response);
        $entries = $this->contentful->getEntries([new EqualFilter(new SystemProperty('id'), 'nyancat')]);
        $this->assertInstanceOf('Markup\Contentful\ResourceArray', $entries);
        $this->assertCount(1, $entries);
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $entry);
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $entry['bestFriend']);
    }

    public function testCacheMissDoesFetch()
    {
        $response = $this->getSuccessMockResponse($this->getEntriesData(), '235345lj34h53j4h');
        $this->mockAdapter->setResponse($response);
        $expectedCacheKey = 'jskdfjhsdfk-entries-(equal)fields.old:6,(less_than)fields.ghosts[lt]:6';
        $cachePool = $this->getMockCachePool();
        $cacheItem = $this->getMockCacheItem();
        $cachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->once()
            ->andReturn($cacheItem);
        $cacheItem
            ->shouldReceive('isHit')
            ->once()
            ->andReturn(false);
        $cacheItem
            ->shouldReceive('set')
            ->once();
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool]]);
        $contentful = new Contentful($spaces, $this->options);
        $filters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $contentful->getEntries($filters);
    }

    public function testCacheHitUsesCacheAndDoesNotFetch()
    {
        $response = $this->getExplodyResponse();
        $this->mockAdapter->setResponse($response);
        $expectedCacheKey = 'jskdfjhsdfk-entries-(equal)fields.old:6,(less_than)fields.ghosts[lt]:6';
        $cachePool = $this->getMockCachePool();
        $cacheItem = $this->getMockCacheItem();
        $cachePool
            ->shouldReceive('getItem')
            ->with($expectedCacheKey)
            ->once()
            ->andReturn($cacheItem);
        $cacheItem
            ->shouldReceive('isHit')
            ->once()
            ->andReturn(true);
        $cacheItem
            ->shouldReceive('get')
            ->andReturn(json_encode($this->getEntriesData()));
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool]]);
        $contentful = new Contentful($spaces, $this->options);
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $entries = $contentful->getEntries($parameters);
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $entry);
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $entry['bestFriend']);
    }

    public function testUsesFallbackCacheOnRequestFailure()
    {
        $response = $this->getExplodyResponse();
        $this->mockAdapter->setResponse($response);
        $expectedCacheKey = 'jskdfjhsdfk-entries-(equal)fields.old:6,(less_than)fields.ghosts[lt]:6';
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
        $contentful = new Contentful($spaces, $this->options);
        $parameters = [new LessThanFilter(new FieldProperty('ghosts'), 6), new EqualFilter(new FieldProperty('old'), 6)];
        $entries = $contentful->getEntries($parameters);
        $entry = array_values(iterator_to_array($entries))[0];
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $entry);
        $this->assertInstanceOf('Markup\Contentful\EntryInterface', $entry['bestFriend']);
    }

    public function testFlushCache()
    {
        $cachePool = $this->getMockCachePool();
        $cachePool
            ->shouldReceive('clear')
            ->once();
        $spaces = array_merge_recursive($this->spaces, ['test' => ['cache' => $cachePool]]);
        $contentful = new Contentful($spaces, $this->options);
        $contentful->flushCache('test');
    }



    public function testResolveContentTypeLink()
    {
        $response = $this->getSuccessMockResponse($this->getContentTypeData(), '235345lj34h53j4h');
        $this->mockAdapter->setResponse($response);
        $data = [
            'type' => 'Link',
            'linkType' => 'ContentType',
            'id' => '45jhb6kjehgej4h5',
        ];
        $metadata = new Metadata();
        $metadata->setType($data['type']);
        $metadata->setLinkType($data['linkType']);
        $metadata->setId($data['id']);
        $link = new Link($metadata);
        $contentType = $this->contentful->resolveLink($link);
        $this->assertInstanceOf('Markup\Contentful\ContentTypeInterface', $contentType);
        $this->assertEquals('cat', $contentType->getId());//of course, in a real situation this would be the same as the ID in the link - but this is the ID in the mock data
        $this->assertEquals('Name', $contentType->getDisplayField()->getName());
    }

    public function testQueryIsLoggedIfLoggerTrue()
    {
        $response = $this->getSuccessMockResponse($this->getEntriesData(), '235345lj34h53j4h');
        $this->mockAdapter->setResponse($response);
        $contentful = new Contentful($this->spaces, array_merge($this->options, ['logger' => true]));
        $entries = $contentful->getEntries([new EqualFilter(new SystemProperty('id'), 'nyancat')]);
        $logs = $contentful->getLogs();
        $this->assertCount(1, $logs);
        $this->assertContainsOnlyInstancesOf('Markup\Contentful\Log\LogInterface', $logs);
        $log = reset($logs);
        $this->assertEquals(LogInterface::RESOURCE_ENTRY, $log->getResourceType());
    }

    private function getSuccessMockResponse($data, $accessToken)
    {
        return function (TransactionInterface $transaction) use ($data, $accessToken) {
            $request = $transaction->getRequest();
            $this->assertEquals('Bearer ' . $accessToken, $request->getHeader('Authorization'));

            return new Response(200, [], Stream::factory(json_encode($data)));
        };
    }

    private function getExplodyResponse()
    {
        return function (TransactionInterface $transaction) {
            $this->fail();
        };
    }

    private function getMockCachePool()
    {
        return m::mock('Psr\Cache\CacheItemPoolInterface')->shouldIgnoreMissing();
    }

    private function getMockCacheItem()
    {
        return m::mock('Psr\Cache\CacheItemInterface')->shouldIgnoreMissing();
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
}
