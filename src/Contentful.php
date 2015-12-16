<?php

namespace Markup\Contentful;

use GuzzleHttp\Adapter\AdapterInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Markup\Contentful\Cache\NullCacheItemPool;
use Markup\Contentful\Decorator\AssetDecoratorInterface;
use Markup\Contentful\Decorator\NullAssetDecorator;
use Markup\Contentful\Exception\LinkUnresolvableException;
use Markup\Contentful\Exception\ResourceUnavailableException;
use Markup\Contentful\Filter\ContentTypeFilterProvider;
use Markup\Contentful\Filter\ContentTypeNameFilter;
use Markup\Contentful\Filter\IsArchivedFilter;
use Markup\Contentful\Log\LoggerInterface;
use Markup\Contentful\Log\LogInterface;
use Markup\Contentful\Log\NullLogger;
use Markup\Contentful\Log\StandardLogger;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Contentful
{
    const CONTENT_DELIVERY_API = 'cda';
    const CONTENT_MANAGEMENT_API = 'cma';
    const PREVIEW_API = 'preview';

    /**
     * @var array
     */
    private $spaces;

    /**
     * @var GuzzleClient
     */
    private $guzzle;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $defaultIncludeLevel;

    /**
     * @var bool
     */
    private $useDynamicEntries;

    /**
     * @var bool
     */
    private $cacheFailResponses;

    /**
     * @var bool
     */
    private $excludeArchived;

    /**
     * @var ResourceEnvelope
     */
    private $envelope;

    /**
     * @param array $spaces A list of known spaces keyed by an arbitrary name. The space array must be a hash with:
     *                      'key', 'access_token'
     *                      and, optionally:
     *                      an 'api_domain' value
     *                      'cache'/'fallback_cache' values which are caches that follow PSR-6 (a fallback cache is for when lookups on the API fail)
     *                      a 'preview_mode' value (Boolean) which determines whether read requests should use the Preview API (i.e. view draft items)
     *                      a 'retry_time_after_rate_limit_in_ms' value, which is the number of milliseconds after which a new request will be issued after a 429 (rate limit) response from the Contentful API (default: 750ms) - a falsy value here will mean no retry
     *                      an 'asset_decorator' value, which must be an object implementing AssetDecoratorInterface - any asset being generated in this space will be decorated by this on the way out
     *                      a 'cache_fail_responses' value, which is a boolean defaulting to FALSE - this should be set to true in a production mode to prevent repeated calls against nonexistent resources
     *                      an 'exclude_archived' value, which is a boolean defaulting to FALSE - would want to set this to true in production so archived entries are not fetched in queries
     * @param array $options A set of options, including 'guzzle_adapter' (a Guzzle adapter object), 'guzzle_event_subscribers' (a list of Guzzle event subscribers to attach), 'guzzle_timeout' (a number of seconds to set as the timeout for lookups using Guzzle) and 'include_level' (the levels of linked content to include in responses by default)
     */
    public function __construct(array $spaces, array $options = [])
    {
        $this->spaces = $spaces;
        $guzzleOptions = GuzzleOptions::createForEnvironment($options);

        $this->guzzle = new GuzzleClient($guzzleOptions->toArray());

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
        $this->cacheFailResponses = (isset($options['cache_fail_responses'])) ? (bool) $options['cache_fail_responses'] : false;
        $this->excludeArchived = (isset($options['exclude_archived'])) ? (bool) $options['exclude_archived'] : false;
        if (!isset($options['logger']) || false === $options['logger']) {
            $this->logger = new NullLogger();
        } else {
            $this->logger = ($options['logger'] instanceof LoggerInterface) ? $options['logger'] : new StandardLogger();
        }
        $this->envelope = new ResourceEnvelope();
    }

    /**
     * @param string|SpaceInterface $space The space name, or space object.
     * @return SpaceInterface
     * @throws Exception\ResourceUnavailableException
     */
    public function getSpace($space = null, array $options = [])
    {
        if ($space instanceof SpaceInterface) {
            return $space;
        } else {
            $spaceName = $space;
        }
        $spaceData = $this->getSpaceDataForName($spaceName);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        return $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s', $spaceData['key']), $api),
            sprintf('The space "%s" was unavailable.', $spaceName),
            $api,
            'space',
            '',
            [],
            $options
        );
    }

    /**
     * @param string $id
     * @param string|SpaceInterface $spaceName
     * @param array  $options A set of options for the fetch, including 'include_level' being how many levels to include
     * @return EntryInterface
     * @throws Exception\ResourceUnavailableException
     */
    public function getEntry($id, $space = null, array $options = [])
    {
        if ($this->envelope->hasEntry($id)) {
            return $this->envelope->findEntry($id);
        }
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName(($space instanceof SpaceInterface) ? $space->getName() : $space);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        return $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/entries/%s', $spaceData['key'], $id), $api),
            sprintf('The entry with ID "%s" from the space "%s" was unavailable.', $id, $spaceName),
            $api,
            'entry',
            strval($id),
            [],
            $options
        );
    }

    /**
     * @param ParameterInterface[] $parameters
     * @param string               $spaceName
     * @param array                $options
     * @return ResourceArray|EntryInterface[]
     * @throws Exception\ResourceUnavailableException
     */
    public function getEntries(array $parameters = [], $space = null, array $options = [])
    {
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName($spaceName);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        return $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/entries', $spaceData['key']), $api),
            sprintf('The entries from the space "%s" were unavailable.', $spaceName),
            $api,
            'entries',
            '',
            $parameters,
            $options
        );
    }

    /**
     * @param string                $id
     * @param string|SpaceInterface $space
     * @return AssetInterface
     */
    public function getAsset($id, $space = null, array $options = [])
    {
        if ($this->envelope->hasAsset($id)) {
            return $this->envelope->findAsset($id);
        }
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName($spaceName);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        return $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/assets/%s', $spaceData['key'], $id), $api),
            sprintf('The asset with ID "%s" from the space "%s" was unavailable.', $id, $spaceName),
            $api,
            'asset',
            strval($id),
            [],
            $options
        );
    }

    /**
     * @param string                $id
     * @param string|SpaceInterface $space
     * @return ContentTypeInterface
     */
    public function getContentType($id, $space = null, array $options = [])
    {
        if ($this->envelope->hasContentType($id)) {
            return $this->envelope->findContentType($id);
        }
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName(($space instanceof SpaceInterface) ? $space->getName() : $space);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        return $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/content_types/%s', $spaceData['key'], $id), $api),
            sprintf('The content type with ID "%s" from the space "%s" was unavailable.', $id, $spaceName),
            $api,
            'content_type',
            strval($id),
            [],
            $options
        );
    }

    /**
     * @param array                 $parameters
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ResourceArray|ContentTypeInterface[]
     */
    public function getContentTypes(array $parameters = [], $space = null, array $options = [])
    {
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName(($space instanceof SpaceInterface) ? $space->getName() : $space);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        return $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/content_types', $spaceData['key']), $api),
            sprintf('The content types from the space "%s" were unavailable.', $spaceName),
            $api,
            'content_types',
            '',
            $parameters,
            $options
        );
    }

    /**
     * Gets a content type using its name. Assumes content types have unique names. Returns null if no content type with the given name can be found.
     *
     * @param string                $name
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ContentTypeInterface|null
     */
    public function getContentTypeByName($name, $space = null, array $options = [])
    {
        $contentTypes = $this->getContentTypes([], $space, $options);
        foreach ($contentTypes as $contentType) {
            if ($contentType->getName() === $name) {
                return $contentType;
            }
        }

        return null;
    }

    /**
     * @param Link $link
     * @return ResourceInterface
     */
    public function resolveLink($link, array $options = [])
    {
        //check whether the "link" is already actually a resolved resource
        if ($link instanceof ResourceInterface) {
            return $link;
        }
        try {
            switch ($link->getLinkType()) {
                case 'Entry':
                    return $this->getEntry($link->getId(), $link->getSpaceName(), $options);
                case 'Asset':
                    return $this->getAsset($link->getId(), $link->getSpaceName(), $options);
                case 'ContentType':
                    return $this->getContentType($link->getId(), $link->getSpaceName(), $options);
                default:
                    throw new \InvalidArgumentException(sprintf('Tried to resolve unknown link type "%s".', $link->getLinkType()));
            }
        } catch (ResourceUnavailableException $e) {
            throw new LinkUnresolvableException($link);
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
     * Gets any collected logs.
     *
     * @return array<LogInterface>
     */
    public function getLogs()
    {
        return $this->logger->getLogs();
    }

    /**
     * @param array                $spaceData
     * @param string               $spaceName
     * @param string               $endpointUrl
     * @param string               $exceptionMessage
     * @param string               $api
     * @param string               $queryType The query type - e.g. "entries" for getEntries(), "asset" for getAsset(), etc
     * @param string               $cacheDisambiguator A string that can disambiguate the individual query, beyond any parameter provided
     * @param ParameterInterface[] $parameters
     * @return ResourceInterface
     */
    private function doRequest($spaceData, $spaceName, $endpointUrl, $exceptionMessage, $api, $queryType = null, $cacheDisambiguator = '', array $parameters, array $options)
    {
        $timer = $this->logger->getStartedTimer();
        $options = $this->mergeOptions($options);
        //ensure parameters are complete first of all as cache keys are generated from them
        $parameters = $this->completeParameters($parameters, $spaceName);
        //exclude archived entries by applying a filter
        if ($options['exclude_archived']) {
            //if there is a userland is archived filter, don't add one
            if (!$this->checkParametersContainIsArchivedFilter($parameters)) {
                $parameters[] = new IsArchivedFilter(false);
            }
        }
        //only use cache if this is a Content Delivery API request
        $cacheKey = $this->generateCacheKey($spaceData['key'], $queryType, $api === self::PREVIEW_API, $cacheDisambiguator, $parameters);
        $cache = $this->ensureCache($spaceData['cache']);
        $cacheItem = $cache->getItem($cacheKey);
        $fallbackCache = $this->ensureCache($spaceData['fallback_cache']);
        $getItemFromCache = function (CacheItemPoolInterface $pool) use ($cacheKey) {
            return $pool->getItem($cacheKey);
        };
        $assetDecorator = $this->ensureAssetDecorator($spaceData['asset_decorator']);
        if ($api !== self::CONTENT_MANAGEMENT_API && $cacheItem->isHit()) {
            $cacheItemJson = $cacheItem->get();
            if (is_string($cacheItemJson) && strlen($cacheItemJson) > 0) {
                $cacheItemData = json_decode($cacheItemJson, $assoc = true);
                //if we are caching fail responses, and this cache item has null content, it's a fail
                if (null !== $cacheItemData) {
                    $this->logger->log(sprintf('Fetched response from cache for key "%s".', $cacheKey), true, $timer, LogInterface::TYPE_RESPONSE, $this->getLogResourceTypeForQueryType($queryType), $api);

                    return $this->buildResponseFromRaw(json_decode($cacheItemJson, $assoc = true), $spaceData['name'], $assetDecorator);
                } elseif ($this->cacheFailResponses) {
                    /**
                     * @var CacheItemInterface $fallbackCacheItem
                     */
                    $fallbackCacheItem = $getItemFromCache($fallbackCache);
                    if ($api === self::CONTENT_DELIVERY_API && $fallbackCacheItem->isHit()) {
                        $fallbackJson = $fallbackCacheItem->get();
                        if (is_string($fallbackJson) && $fallbackJson !== json_encode(null) && strlen($fallbackJson) > 0) {
                            $this->logger->log(
                                sprintf('Used successful fallback cache value as main cache has a fail response for key "%s".', $cacheKey),
                                true,
                                $timer,
                                LogInterface::TYPE_RESOURCE,
                                $this->getLogResourceTypeForQueryType($queryType),
                                $api
                            );
                            return $this->buildResponseFromRaw(json_decode($fallbackJson, $assoc = true), $spaceData['name'], $assetDecorator);
                        }
                    }
                    throw new ResourceUnavailableException(null, sprintf('Fetched fail response from cache for key "%s".', $cacheKey));
                }
            }
        }
        $request = $this->guzzle->createRequest('GET', $endpointUrl);
        $this->setAuthHeaderOnRequest($request, $spaceData['access_token']);
        $this->setApiVersionHeaderOnRequest($request, $api);
        //set the include level
        if (null !== $options['include_level']) {
            $request->getQuery()->set('include', $options['include_level']);
        }
        //set parameters onto the request
        foreach ($parameters as $param) {
            /**
             * @var ParameterInterface $param
             */
            $request->getQuery()->set($param->getKey(), $param->getValue());
        }

        $unavailableException = null;
        try {
            /**
             * @var ResponseInterface $response
             */
            $response = $this->guzzle->send($request);
        } catch (RequestException $e) {
            /**
             * @var CacheItemInterface $fallbackCacheItem
             */
            $fallbackCacheItem = $getItemFromCache($fallbackCache);
            if (in_array($api, [self::CONTENT_DELIVERY_API, self::PREVIEW_API]) && $fallbackCacheItem->isHit()) {
                $fallbackJson = $fallbackCacheItem->get();
                if (is_string($fallbackJson) && $fallbackJson !== json_encode(null) && strlen($fallbackJson) > 0) {
                    $this->logger->log(
                        sprintf('Fetched response from fallback cache for key "%s".', $cacheKey),
                        true,
                        $timer,
                        LogInterface::TYPE_RESOURCE,
                        $this->getLogResourceTypeForQueryType($queryType),
                        $api
                    );
                    //save fallback value into main cache
                    $cacheItem->set($fallbackJson);
                    $cache->save($cacheItem);

                    return $this->buildResponseFromRaw(json_decode($fallbackJson, $assoc = true), $spaceData['name'], $assetDecorator);
                }
            }
            //if there is a rate limit error, wait (if applicable)
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === '429' && $spaceData['retry_time_after_rate_limit_in_ms']) {
                usleep(intval($spaceData['retry_time_after_rate_limit_in_ms']));

                return $this->doRequest($spaceData, $spaceName, $endpointUrl, $exceptionMessage, $api, $queryType, $cacheDisambiguator, $parameters, $options);
            }
            $unavailableException = new ResourceUnavailableException($e->getResponse(), $exceptionMessage, 0, $e);
        }
        if (!$unavailableException && $response->getStatusCode() != '200') {
            $unavailableException = new ResourceUnavailableException(
                $response,
                sprintf(
                    $exceptionMessage . ' Contentful returned a "%s - %s" response.',
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                )
            );
        }
        //if we aren't caching fail responses, and there is an unavailable exception, throw it
        if (!$this->cacheFailResponses && $unavailableException instanceof \Exception) {
            throw $unavailableException;
        }
        //save into cache
        if ($api !== self::CONTENT_MANAGEMENT_API) {
            $responseJson = json_encode((!$unavailableException) ? $response->json() : null);
            $isSuccessResponseData = !$unavailableException;
            $cacheItem->set($responseJson);
            $cache->save($cacheItem);
            if (!isset($fallbackCacheItem)) {
                /**
                 * @var CacheItemInterface $fallbackCacheItem
                 */
                $fallbackCacheItem = $getItemFromCache($fallbackCache);
            }
            if ((!$unavailableException || $fallbackCacheItem->get() === null) && $isSuccessResponseData) {
                $fallbackCacheItem->set($responseJson);
                $fallbackCache->save($fallbackCacheItem);
            }
        }
        if ($unavailableException instanceof \Exception) {
            throw $unavailableException;
        }
        $this->logger->log(sprintf('Fetched a fresh response from URL "%s".', $request->getUrl()), false, $timer, LogInterface::TYPE_RESPONSE, $this->getLogResourceTypeForQueryType($queryType), $api);


        return $this->buildResponseFromRaw($response->json(), $spaceData['name'], $assetDecorator);
    }

    /**
     * @param string $queryType
     * @return string
     */
    private function getLogResourceTypeForQueryType($queryType)
    {
        $map = [
            'entry' => LogInterface::RESOURCE_ENTRY,
            'entries' => LogInterface::RESOURCE_ENTRY,
            'asset' => LogInterface::RESOURCE_ASSET,
            'assets' => LogInterface::RESOURCE_ASSET,
            'content_type' => LogInterface::RESOURCE_CONTENT_TYPE,
        ];
        if (!isset($map[$queryType])) {
            return null;
        }

        return $map[$queryType];
    }

    private function getSpaceDataForName($spaceName = null)
    {
        $defaultData = [
            'cache' => null,
            'fallback_cache' => null,
            'preview_mode' => false,
            'retry_time_after_rate_limit_in_ms' => 750,
            'asset_decorator' => null,
        ];
        if ($spaceName) {
            if (!array_key_exists($spaceName, $this->spaces)) {
                throw new \InvalidArgumentException(sprintf('The space with name "%s" is not known to this client.', $spaceName));
            }

            return array_merge($defaultData, $this->spaces[$spaceName], ['name' => $spaceName]);
        }

        $firstKey = array_keys($this->spaces)[0];

        return array_merge(
            $defaultData,
            array_values($this->spaces)[0],
            [
                'name' => (!is_numeric($firstKey)) ? $firstKey : null
            ]
        );
    }

    /**
     * @param string $api An *_API value.
     * @return string
     */
    private function getDomainForApi($api)
    {
        $domainMap = [
            self::CONTENT_DELIVERY_API => 'cdn.contentful.com',
            self::PREVIEW_API => 'preview.contentful.com',
            self::CONTENT_MANAGEMENT_API => 'api.contentful.com',
        ];

        return $domainMap[$api];
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
     * @param string $spaceName
     * @param AssetDecoratorInterface $assetDecorator
     * @return ResourceInterface
     */
    private function buildResponseFromRaw(array $data, $spaceName = null, AssetDecoratorInterface $assetDecorator = null)
    {
        static $resourceBuilder;
        if (empty($resourceBuilder)) {
            $resourceBuilder = new ResourceBuilder($this->envelope);
            $resourceBuilder->setResolveLinkFunction(function ($link) {
                return $this->resolveLink($link);
            });
            $resourceBuilder->setUseDynamicEntries($this->useDynamicEntries);
        }

        return $resourceBuilder->buildFromData($data, $spaceName, $assetDecorator ?: new NullAssetDecorator());
    }

    /**
     * @param array $options
     * @return array
     */
    private function mergeOptions(array $options)
    {
        $defaultOptions = [
            'include_level' => $this->defaultIncludeLevel,
            'exclude_archived' => $this->excludeArchived,
        ];

        return array_merge($defaultOptions, $options);
    }

    /**
     * @param string $spaceKey
     * @param string $queryType
     * @param bool   $isPreview
     * @param string $disambiguator
     * @param ParameterInterface[] $parameters
     * @return string
     */
    private function generateCacheKey($spaceKey, $queryType, $isPreview, $disambiguator, array $parameters = [])
    {
        $key = $spaceKey . '-' . $queryType . (($isPreview) ? '-preview' : '');
        if ($disambiguator) {
            $key .= ':' . $disambiguator;
        }
        if (count($parameters) > 0) {
            //sort parameters by name, then key
            $paramSort = function (ParameterInterface $param1, ParameterInterface $param2) {
                $nameCompare = strcmp($param1->getName(), $param2->getName());
                if ($nameCompare !== 0) {
                    return $nameCompare;
                }

                return strcmp($param1->getKey(), $param2->getKey());
            };
            usort($parameters, $paramSort);
            $key .= '-' . implode(',', array_map(function (ParameterInterface $param) {
                return sprintf('(%s)%s:%s', $param->getName(), $param->getKey(), $param->getValue());
            }, $parameters));
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

    /**
     * Ensures a cache by passing through a passed in asset decorator, or returning a null asset decorator if arg is not an asset decorator.
     *
     * @param mixed $candidate
     * @return AssetDecoratorInterface
     */
    private function ensureAssetDecorator($candidate)
    {
        if (!$candidate instanceof AssetDecoratorInterface) {
            return new NullAssetDecorator();
        }

        return $candidate;
    }

    /**
     * @param ParameterInterface[] $parameters
     * @param string               $spaceName
     * @return ParameterInterface[]
     */
    private function completeParameters($parameters, $spaceName)
    {
        $complete = [];
        foreach ($parameters as $parameter) {
            if (!$parameter instanceof ParameterInterface) {
                continue;
            }
            $complete[] = $this->completeParameter($parameter, $spaceName);
        }

        return $complete;
    }

    /**
     * Ensures that the provided parameter is complete.
     *
     * @param ParameterInterface $parameter
     * @param string             $spaceName
     * @return ParameterInterface
     */
    private function completeParameter(ParameterInterface $parameter, $spaceName = null)
    {
        if (!$parameter instanceof IncompleteParameterInterface) {
            return $parameter;
        }

        switch ($parameter->getName()) {
            case 'content_type_name':
                $contentTypeFilter = $this->resolveContentTypeNameFilter($parameter, $spaceName);
                if (null === $contentTypeFilter) {
                    throw new \RuntimeException(sprintf('Could not resolve content type with name "%s".', $parameter->getValue()));
                }

                return $contentTypeFilter;
                break;
            default:
                throw new \LogicException(sprintf('Unknown incomplete parameter of type "%s" is being used.', $parameter->getName()));
                break;
        }
    }

    /**
     * @param ContentTypeNameFilter $filter
     * @param string                $spaceName
     * @return ParameterInterface
     */
    private function resolveContentTypeNameFilter(ContentTypeNameFilter $filter, $spaceName = null)
    {
        static $contentTypeFilterProvider;
        if (empty($contentTypeFilterProvider)) {
            $contentTypeFilterProvider = new ContentTypeFilterProvider($this);
        }

        return $contentTypeFilterProvider->createForContentTypeName($filter->getValue(), $spaceName);
    }

    /**
     * @param ParameterInterface[] $parameters
     */
    private function checkParametersContainIsArchivedFilter($parameters)
    {
        foreach ($parameters as $parameter) {
            if ($parameter instanceof IsArchivedFilter) {
                return true;
            }
        }

        return false;
    }
}
