<?php

namespace Markup\Contentful\Tests;

use GuzzleHttp\Adapter\MockAdapter;
use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Markup\Contentful\Contentful;
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

    private function getSuccessMockResponse($data, $accessToken)
    {
        return function (TransactionInterface $transaction) use ($data, $accessToken) {
            $request = $transaction->getRequest();
            $this->assertEquals('Bearer ' . $accessToken, $request->getHeader('Authorization'));

            return new Response(200, [], Stream::factory(json_encode($data)));
        };
    }
}
