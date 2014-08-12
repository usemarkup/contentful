<?php

namespace Markup\Contentful;

use GuzzleHttp\Adapter\AdapterInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use Markup\Contentful\Cache\NullCacheItemPool;
use Markup\Contentful\Exception\ResourceUnavailableException;
use Psr\Cache\CacheItemPoolInterface;

class Contentful
{
    const CONTENT_DELIVERY_API = 'cda';
    const CONTENT_MANAGEMENT_API = 'cma';

    /**
     * @var array
     */
    private $spaces;

    /**
     * @var GuzzleClient
     */
    private $guzzle;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $defaultIncludeLevel;

    /**
     * @var bool
     */
    private $useDynamicEntries;

    /**
     * @param array $spaces A list of known spaces keyed by an arbitrary name. The space array must be a hash with 'key', 'access_token' and, optionally, an 'api_domain' value and a 'cache' value which is a cache that follows PSR-6.
     * @param array $options A set of options, including 'guzzle_adapter' (a Guzzle adapter object), 'guzzle_event_subscribers' (a list of Guzzle event subscribers to attach), and 'include_level' (the levels of linked content to include in responses by default)
     */
    public function __construct(array $spaces, array $options = [])
    {
        $this->spaces = $spaces;
        $guzzleOptions = [];
        if (isset($options['guzzle_adapter']) && $options['guzzle_adapter'] instanceof AdapterInterface) {
            $guzzleOptions['adapter'] = $options['guzzle_adapter'];
        }
        $this->guzzle = new GuzzleClient($guzzleOptions);
        if (isset($options['guzzle_event_subscribers'])) {
            $emitter = $this->guzzle->getEmitter();
            foreach ($options['guzzle_event_subscribers'] as $subscriber) {
                if (!$subscriber instanceof SubscriberInterface) {
                    continue;
                }
                $emitter->attach($subscriber);
            }
        }
        $this->useDynamicEntries = !isset($options['dynamic_entries']) || $options['dynamic_entries'];
        $this->defaultIncludeLevel = (isset($options['include_level'])) ? intval($options['include_level']) : 0;
    }

    /**
     * @param string $spaceName
     * @return SpaceInterface
     * @throws Exception\ResourceUnavailableException
     */
    public function getSpace($spaceName = null, array $options = [])
    {
        $spaceData = $this->getSpaceDataForName($spaceName);

        return $this->doRequest(
            $spaceData,
            $this->getEndpointUrl(sprintf('/spaces/%s', $spaceData['key']), self::CONTENT_DELIVERY_API),
            sprintf('The space "%s" was unavailable.', $spaceName),
            self::CONTENT_DELIVERY_API,
            'space',
            [],
            $options
        );
    }

    /**
     * @param string $id
     * @param string $spaceName
     * @param array  $options A set of options for the fetch, including 'include_level' being how many levels to include
     * @return EntryInterface
     * @throws Exception\ResourceUnavailableException
     */
    public function getEntry($id, $spaceName = null, array $options = [])
    {
        $spaceData = $this->getSpaceDataForName($spaceName);

        return $this->doRequest(
            $spaceData,
            $this->getEndpointUrl(sprintf('/spaces/%s/entries/%s', $spaceData['key'], $id), self::CONTENT_DELIVERY_API),
            sprintf('The entry with ID "%s" from the space "%s" was unavailable.', $id, $spaceName),
            self::CONTENT_DELIVERY_API,
            'entry',
            [],
            $options
        );
    }

    /**
     * @param FilterInterface $filters
     * @param string          $spaceName
     * @param array           $options
     * @return EntryInterface[]
     * @throws Exception\ResourceUnavailableException
     */
    public function getEntries(array $filters = [], $spaceName = null, array $options = [])
    {
        $spaceData = $this->getSpaceDataForName($spaceName);

        return $this->doRequest(
            $spaceData,
            $this->getEndpointUrl(sprintf('/spaces/%s/entries', $spaceData['key']), self::CONTENT_DELIVERY_API),
            sprintf('The entries from the space "%s" were unavailable.', $spaceName),
            self::CONTENT_DELIVERY_API,
            'entries',
            $filters,
            $options
        );
    }

    /**
     * @param string $id
     * @param string $spaceName
     * @return AssetInterface
     */
    public function getAsset($id, $spaceName = null, array $options = [])
    {
        $spaceData = $this->getSpaceDataForName($spaceName);

        return $this->doRequest(
            $spaceData,
            $this->getEndpointUrl(sprintf('/spaces/%s/assets/%s', $spaceData['key'], $id), self::CONTENT_DELIVERY_API),
            sprintf('The asset with ID "%s" from the space "%s" was unavailable.', $id, $spaceName),
            self::CONTENT_DELIVERY_API,
            'asset',
            [],
            $options
        );
    }

    /**
     * @param string $id
     * @param string $spaceName
     * @return ContentTypeInterface
     */
    public function getContentType($id, $spaceName = null, array $options = [])
    {
        $spaceData = $this->getSpaceDataForName($spaceName);

        return $this->doRequest(
            $spaceData,
            $this->getEndpointUrl(sprintf('/spaces/%s/content_types/%s', $spaceData['key'], $id), self::CONTENT_DELIVERY_API),
            sprintf('The content type with ID "%s" from the space "%s" was unavailable.', $id, $spaceName),
            self::CONTENT_DELIVERY_API,
            'content_type',
            [],
            $options
        );
    }

    /**
     * @param Link $link
     * @return ResourceInterface
     */
    public function resolveLink(Link $link, $spaceName = null, array $options = [])
    {
        switch ($link->getLinkType()) {
            case 'Entry':
                return $this->getEntry($link->getId(), $spaceName, $options);
            case 'Asset':
                return $this->getAsset($link->getId(), $spaceName, $options);
            case 'ContentType':
                return $this->getContentType($link->getId(), $spaceName, $options);
            default:
                throw new \InvalidArgumentException(sprintf('Tried to resolve unknown link type "%s".', $link->getLinkType()));
        }
    }

    /**
     * Flushes the whole cache. Returns true if flush was successful, false otherwise.
     *
     * @return bool
     */
    public function flushCache($spaceName = null)
    {
        $spaceData = $this->getSpaceDataForName($spaceName);

        return $this->ensureCache($spaceData['cache'])->clear();
    }

    /**
     * @param array             $spaceData
     * @param string            $endpointUrl
     * @param string            $exceptionMessage
     * @param string            $api
     * @param string            $queryType The query type - e.g. "entries" for getEntries(), "asset" for getAsset(), etc
     * @param FilterInterface[] $filters
     * @return ResourceInterface
     */
    private function doRequest($spaceData, $endpointUrl, $exceptionMessage, $api, $queryType = null, array $filters, array $options)
    {
        $options = $this->mergeOptions($options);
        //only use cache if this is a Content Delivery API request
        $cacheKey = $this->generateCacheKey($spaceData['key'], $queryType, $filters);
        $cache = $this->ensureCache($spaceData['cache']);
        $cacheItem = $cache->getItem($cacheKey);
        if ($api === self::CONTENT_DELIVERY_API && $cacheItem->isHit()) {
            return $this->buildResponseFromRaw(json_decode($cacheItem->get(), $assoc = true));
        }
        $request = $this->guzzle->createRequest('GET', $endpointUrl);
        $this->setAuthHeaderOnRequest($request, $spaceData['access_token']);
        $this->setApiVersionHeaderOnRequest($request, $api);
        //set the include level
        if (null !== $options['include_level']) {
            $request->getQuery()->set('include', $options['include_level']);
        }
        //set filters onto the request
        foreach ($filters as $filter) {
            /**
             * @var FilterInterface $filter
             */
            $request->getQuery()->set($filter->getKey(), $filter->getValue());
        }

        try {
            $response = $this->guzzle->send($request);
        } catch (RequestException $e) {
            throw new ResourceUnavailableException($e->getResponse(), $exceptionMessage, 0, $e);
        }
        if ($response->getStatusCode() !== '200') {
            throw new ResourceUnavailableException(
                $response,
                sprintf(
                    $exceptionMessage . ' Contentful returned a "%s - %s" response.',
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                )
            );
        }
        //save into cache
        if ($api === self::CONTENT_DELIVERY_API) {
            $cacheItem->set(json_encode($response->json()));
            $cache->save($cacheItem);
        }

        return $this->buildResponseFromRaw($response->json());
    }

    private function getSpaceDataForName($spaceName = null)
    {
        $defaultData = ['cache' => null];
        if ($spaceName) {
            if (!array_key_exists($spaceName, $this->spaces)) {
                throw new \InvalidArgumentException(sprintf('The space with name "%s" is not known to this client.', $spaceName));
            }

            return array_merge($defaultData, $this->spaces[$spaceName]);
        }

        return array_merge($defaultData, array_values($this->spaces)[0]);
    }

    /**
     * @param string $api An *_API value.
     * @return string
     */
    private function getDomainForApi($api)
    {
        return ($api === self::CONTENT_MANAGEMENT_API) ? 'api.contentful.com' : 'cdn.contentful.com';
    }

    /**
     * @param string $path
     * @param string $api
     * @return string
     */
    private function getEndpointUrl($path, $api)
    {
        return sprintf('https://%s%s', $this->getDomainForApi($api), $path);
    }

    /**
     * @param RequestInterface $request
     * @param                  $accessToken
     */
    private function setAuthHeaderOnRequest(RequestInterface $request, $accessToken)
    {
        $request->setHeader('Authorization', sprintf('Bearer %s', $accessToken));
    }

    /**
     * @param RequestInterface $request
     */
    private function setApiVersionHeaderOnRequest(RequestInterface $request, $api)
    {
        //specify version 1 header
        $request->setHeader('Content-Type', sprintf('application/vnd.contentful.%s.v1+json', ($api === self::CONTENT_MANAGEMENT_API) ? 'management' : 'delivery'));
    }

    /**
     * @param array $data
     * @return ResourceInterface
     */
    private function buildResponseFromRaw(array $data)
    {
        static $resourceBuilder;
        if (empty($resourceBuilder)) {
            $resourceBuilder = new ResourceBuilder();
            $resourceBuilder->setResolveLinkFunction(function (Link $link) {
                return $this->resolveLink($link);
            });
            $resourceBuilder->setUseDynamicEntries($this->useDynamicEntries);
        }

        return $resourceBuilder->buildFromData($data);
    }

    private function mergeOptions(array $options)
    {
        $defaultOptions = [
            'include_level' => $this->defaultIncludeLevel,
        ];

        return array_merge($defaultOptions, $options);
    }

    /**
     * @param string $spaceKey
     * @param string $queryType
     * @param FilterInterface[] $filters
     * @return string
     */
    private function generateCacheKey($spaceKey, $queryType, array $filters = [])
    {
        $key = $spaceKey . '-' . $queryType;
        if (count($filters) > 0) {
            //sort filters by name, then key
            $filterSort = function (FilterInterface $filter1, FilterInterface $filter2) {
                $nameCompare = strcmp($filter1->getName(), $filter2->getName());
                if ($nameCompare !== 0) {
                    return $nameCompare;
                }

                return strcmp($filter1->getKey(), $filter2->getKey());
            };
            usort($filters, $filterSort);
            $key .= '-' . implode(',', array_map(function (FilterInterface $filter) {
                return sprintf('(%s)%s:%s', $filter->getName(), $filter->getKey(), $filter->getValue());
            }, $filters));
        }

        return $key;
    }

    /**
     * Ensures a cache by passing through a passed in cache, or returning a null cache if arg is not a cache.
     *
     * @param mixed $candidate
     * @return CacheItemPoolInterface
     */
    private function ensureCache($candidate)
    {
        if (!$candidate instanceof CacheItemPoolInterface) {
            return new NullCacheItemPool();
        }

        return $candidate;
    }
}
