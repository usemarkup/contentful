<?php

namespace Markup\Contentful;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\coroutine;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Markup\Contentful\Analysis\ResponseAnalyzer;
use Markup\Contentful\Cache\NullCacheItemPool;
use Markup\Contentful\Decorator\AssetDecoratorInterface;
use Markup\Contentful\Decorator\NullAssetDecorator;
use Markup\Contentful\Exception\LinkUnresolvableException;
use Markup\Contentful\Exception\ResourceUnavailableException;
use Markup\Contentful\Filter\ContentTypeFilterProvider;
use Markup\Contentful\Filter\DecidesCacheKeyInterface;
use Markup\Contentful\Filter\LinksToAssetFilter;
use Markup\Contentful\Filter\LocaleFilter;
use Markup\Contentful\Log\LinkResolveCounterInterface;
use Markup\Contentful\Log\LoggerInterface;
use Markup\Contentful\Log\LogInterface;
use Markup\Contentful\Log\NullLinkResolveCounter;
use Markup\Contentful\Log\NullLogger;
use Markup\Contentful\Log\StandardLogger;
use Markup\Contentful\Promise\AssetPromise;
use Markup\Contentful\Promise\ContentTypePromise;
use Markup\Contentful\Promise\EntryPromise;
use Markup\Contentful\Promise\ResourceArrayPromise;
use Markup\Contentful\Promise\SpacePromise;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Contentful
{
    use GuzzleAbstractionTrait;

    const CONTENT_DELIVERY_API = 'cda';
    const PREVIEW_API = 'preview';

    /**
     * @var array
     */
    private $spaces;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LinkResolveCounterInterface
     */
    private $linkResolveCounter;

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
     * @var ResourceEnvelopePool
     */
    private $resourcePool;

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
     *                      a 'resource_envelope' value, which must be an object implementing ResourceEnvelopeInterface
     * @param array $options A set of options, including:
     *                      'guzzle_handler' (a Guzzle handler object)
     *                      'guzzle_timeout' (a number of seconds to set as the timeout for lookups using Guzzle)
     *                      'guzzle_proxy' (defines a HTTP Proxy URL which will is used for requesting the Contentful API)
     *                      'include_level' (the levels of linked content to include in responses by default)
     *                      'link_resolve_counter' (an instance of a link resolve counter for counting resolved links)
     */
    public function __construct(array $spaces, array $options = [])
    {
        $this->spaces = $spaces;
        $guzzleOptions = GuzzleOptions::createForEnvironment($options);

        $this->guzzle = new GuzzleClient($guzzleOptions->toArray());

        $this->useDynamicEntries = !isset($options['dynamic_entries']) || $options['dynamic_entries'];
        $this->defaultIncludeLevel = (isset($options['include_level'])) ? intval($options['include_level']) : 0;
        $this->cacheFailResponses = (isset($options['cache_fail_responses'])) ? (bool) $options['cache_fail_responses'] : false;
        if (!isset($options['logger']) || false === $options['logger']) {
            $this->logger = new NullLogger();
        } else {
            $this->logger = ($options['logger'] instanceof LoggerInterface) ? $options['logger'] : new StandardLogger();
        }
        if (($options['link_resolve_counter'] ?? null) instanceof LinkResolveCounterInterface) {
            $this->linkResolveCounter = $options['link_resolve_counter'];
        } else {
            $this->linkResolveCounter = new NullLinkResolveCounter();
        }
        $this->resourcePool = new ResourceEnvelopePool();
        foreach ($spaces as $key => $space) {
            $envelope = $space['resource_envelope'] ?? new MemoizedResourceEnvelope();
            $this->setResolveLinkFunctionOntoEnvelope($envelope);
            $this->resourcePool->registerEnvelopeForSpace($envelope, $key);
        }
    }

    /**
     * @param string|SpaceInterface $space The space name, or space object.
     * @param array $options
     * @return SpaceInterface|PromiseInterface
     * @throws Exception\ResourceUnavailableException
     */
    public function getSpace($space, array $options = [])
    {
        if ($space instanceof SpaceInterface) {
            return ($this->isAsyncCall($options))
                ? promise_for($space)
                : $space;
        } else {
            $spaceName = $space;
        }
        $spaceData = $this->getSpaceDataForName($spaceName);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        /** @var SpaceInterface|PromiseInterface $space */
        $space = $this->doRequest(
            $spaceData,
            $spaceName ?: '',
            $this->getEndpointUrl(sprintf('/spaces/%s', $spaceData['key']), $api),
            sprintf('The space "%s" was unavailable.', $spaceName),
            $api,
            'space',
            '',
            [],
            $options
        );

        return $space;
    }

    /**
     * @param string|SpaceInterface $space The space name, or space object.
     * @param array $options
     * @return SpaceInterface|PromiseInterface
     */
    public function getSpaceAsync($space, array $options = [])
    {
        /** @var PromiseInterface $space */
        $space = $this->getSpace($space, array_merge($options, ['async' => true]));

        return new SpacePromise($space);
    }

    /**
     * @param string $id
     * @param string|SpaceInterface $space
     * @param array  $options A set of options for the fetch, including 'include_level' being how many levels to include
     * @param string $locale  A locale for the entry data, if one is specified (otherwise, API will use default locale for the space)
     * @return EntryInterface|PromiseInterface
     * @throws Exception\ResourceUnavailableException
     */
    public function getEntry($id, $space, array $options = [], $locale = null)
    {
        $envelope = $this->findEnvelopeForSpace($space);
        if ($envelope->hasEntry($id, $locale)) {
            /** @var EntryInterface|PromiseInterface $entry */
            $entry = ($this->isAsyncCall($options))
                ? promise_for($envelope->findEntry($id, $locale))
                : $envelope->findEntry($id, $locale);

            return $entry;
        }
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName(($space instanceof SpaceInterface) ? $space->getName() : $space);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        /** @var EntryInterface|PromiseInterface $entry */
        $entry = $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/entries/%s', $spaceData['key'], $id), $api),
            sprintf('The entry with ID "%s" from the space "%s" was unavailable.', $id, $spaceName),
            $api,
            'entry',
            strval($id),
            ($locale) ? [new LocaleFilter($locale)] : [],
            $options
        );

        return $entry;
    }

    /**
     * @param string $id
     * @param string|SpaceInterface $space
     * @param array $options
     * @param string|null $locale
     * @return EntryInterface|PromiseInterface
     */
    public function getEntryAsync($id, $space, array $options = [], $locale = null)
    {
        /** @var PromiseInterface $entry */
        $entry = $this->getEntry($id, $space, array_merge($options, ['async' => true]), $locale);
        /** @var EntryInterface|PromiseInterface $promise */
        $promise = new EntryPromise($entry);

        return $promise;
    }

    /**
     * @param ParameterInterface[]  $parameters
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ResourceArrayInterface|EntryInterface[]|PromiseInterface
     * @throws Exception\ResourceUnavailableException
     */
    public function getEntries(array $parameters, $space, array $options = [])
    {
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName($spaceName);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        /** @var ResourceArrayInterface|EntryInterface[]|PromiseInterface $entries */
        $entries = $this->doRequest(
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

        return $entries;
    }

    /**
     * @param ParameterInterface[] $parameters
     * @param string               $space
     * @param array                $options
     * @return ResourceArrayInterface|PromiseInterface
     */
    public function getEntriesAsync(array $parameters, $space, array $options = [])
    {
        /** @var PromiseInterface $entries */
        $entries = $this->getEntries($parameters, $space, array_merge($options, ['async' => true]));

        return new ResourceArrayPromise($entries);
    }

    /**
     * @param string                $id
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @param string|null           $locale
     * @return AssetInterface|PromiseInterface
     */
    public function getAsset($id, $space, array $options = [], $locale = null)
    {
        $envelope = $this->findEnvelopeForSpace($space);
        if ($envelope->hasAsset($id, $locale)) {
            return ($this->isAsyncCall($options))
                ? promise_for($envelope->findAsset($id, $locale))
                : $envelope->findAsset($id, $locale);
        }
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName($spaceName);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        /** @var AssetInterface|PromiseInterface $asset */
        $asset = $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/assets/%s', $spaceData['key'], $id), $api),
            sprintf('The asset with ID "%s" from the space "%s" was unavailable.', $id, $spaceName),
            $api,
            'asset',
            strval($id),
            ($locale) ? [new LocaleFilter($locale)] : [],
            $options
        );

        return $asset;
    }

    /**
     * @param string                $id
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @param string|null           $locale
     * @return AssetInterface|PromiseInterface
     */
    public function getAssetAsync($id, $space, array $options = [], $locale = null)
    {
        /** @var PromiseInterface $asset */
        $asset = $this->getAsset($id, $space, array_merge($options, ['async' => true]), $locale);

        return new AssetPromise($asset);
    }

    /**
     * @param array $parameters
     * @param string|SpaceInterface $space
     * @param array $options
     * @return PromiseInterface|AssetInterface[]
     */
    public function getAssets(array $parameters, $space, array $options = [])
    {
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        $spaceData = $this->getSpaceDataForName($spaceName);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        /** @var PromiseInterface|AssetInterface[] $assets */
        $assets = $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/assets', $spaceData['key']), $api),
            sprintf('The assets from the space "%s" were unavailable.', $spaceName),
            $api,
            'assets',
            '',
            $parameters,
            $options
        );

        return $assets;
    }

    /**
     * @param array $parameters
     * @param string|SpaceInterface $space
     * @param array $options
     * @return ResourceArrayPromise
     */
    public function getAssetsAsync(array $parameters, $space, array $options = [])
    {
        /** @var PromiseInterface $assets */
        $assets = $this->getAssets($parameters, $space, array_merge($options, ['async' => true]));

        return new ResourceArrayPromise($assets);
    }

    /**
     * @param string $assetId
     * @param string|SpaceInterface $space
     * @return bool
     */
    public function isAssetUnlinked($assetId, $space)
    {
        $filters = [];
        $filters[] = new LinksToAssetFilter($assetId);

        /** @var ResourceArrayInterface $linkedEntries */
        $linkedEntries = $this->getEntries($filters, $space);

        return $linkedEntries->getTotal() === 0;
    }

    /**
     * @param string                $id
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ContentTypeInterface|PromiseInterface
     */
    public function getContentType($id, $space, array $options = [])
    {
        $envelope = $this->findEnvelopeForSpace($space);
        if ($envelope->hasContentType($id)) {
            return ($this->isAsyncCall($options))
                ? promise_for($envelope->findContentType($id))
                : $envelope->findContentType($id);
        }

        //fetch them all and pick one out, as it is likely we'll want to access others
        $contentTypesPromise = promise_for($this->getContentTypes([], $space, $options))
            ->then(
                function ($contentTypes) use ($id, $envelope) {
                    foreach ($contentTypes as $contentType) {
                        $envelope->insertContentType($contentType);
                    }

                    return promise_for($envelope->findContentType($id));
                }
            );

        return (isset($options['async']) && $options['async']) ? $contentTypesPromise : $contentTypesPromise->wait();
    }

    /**
     * @param string                $id
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ContentTypeInterface|PromiseInterface
     */
    public function getContentTypeAsync($id, $space, array $options = [])
    {
        /** @var PromiseInterface $contentType */
        $contentType = $this->getContentType($id, $space, array_merge($options, ['async' => true]));

        return new ContentTypePromise($contentType);
    }

    /**
     * @param array                 $parameters
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ResourceArray|ContentTypeInterface[]|PromiseInterface
     */
    public function getContentTypes(array $parameters, $space, array $options = [])
    {
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;
        if (!$parameters) {
            $stashedContentTypes = $this->findEnvelopeForSpace($spaceName)->getAllContentTypes();
            if (null !== $stashedContentTypes) {
                return ($this->isAsyncCall($options))
                    ? promise_for($stashedContentTypes)
                    : $stashedContentTypes;
            }
        }
        $spaceData = $this->getSpaceDataForName(($space instanceof SpaceInterface) ? $space->getName() : $space);
        $api = ($spaceData['preview_mode']) ? self::PREVIEW_API : self::CONTENT_DELIVERY_API;

        /** @var ResourceArray|ContentTypeInterface[]|PromiseInterface $contentTypes */
        $contentTypes = $this->doRequest(
            $spaceData,
            $spaceName,
            $this->getEndpointUrl(sprintf('/spaces/%s/content_types', $spaceData['key']), $api),
            sprintf('The content types from the space "%s" were unavailable.', $spaceName),
            $api,
            'content_types',
            '',
            $parameters,
            array_merge($options, ['include_level' => null])
        );

        return $contentTypes;
    }

    /**
     * @param array                 $parameters
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ResourceArray|ContentTypeInterface[]|PromiseInterface
     */
    public function getContentTypesAsync(array $parameters, $space, array $options = [])
    {
        /** @var PromiseInterface $contentTypes */
        $contentTypes = $this->getContentTypes($parameters, $space, array_merge($options, ['async' => true]));

        return new ResourceArrayPromise($contentTypes);
    }

    /**
     * Gets a content type using its name. Assumes content types have unique names. Returns null if no content type with the given name can be found.
     *
     * @param string                $name
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ContentTypeInterface|PromiseInterface|null
     */
    public function getContentTypeByName($name, $space, array $options = [])
    {
        $envelope = $this->findEnvelopeForSpace($space);
        $promise = coroutine(
            function () use ($name, $space, $envelope, $options) {
                $contentTypeFromEnvelope = $envelope->findContentTypeByName($name);
                if ($contentTypeFromEnvelope) {
                    yield promise_for($contentTypeFromEnvelope);
                    return;
                }
                $contentTypes = (yield $this->getContentTypes([], $space, array_merge($options, ['async' => true])));
                $foundContentType = null;
                foreach ($contentTypes as $contentType) {
                    if ($contentType->getName() === $name) {
                        $foundContentType = $contentType;
                    }
                    $envelope->insertContentType($contentType);
                }

                yield $foundContentType;
            }
        );

        return (isset($options['async']) && $options['async']) ? $promise : $promise->wait();
    }

    /**
     * Gets a (lazy-fetching) content type using its name. Assumes content types have unique names. Returns null if no content type with the given name can be found.
     *
     * @param string                $name
     * @param string|SpaceInterface $space
     * @param array                 $options
     * @return ContentTypeInterface|PromiseInterface|null
     */
    public function getContentTypeByNameAsync($name, $space, array $options = [])
    {
        /** @var PromiseInterface $contentType */
        $contentType = $this->getContentTypeByName($name, $space, array_merge($options, ['async' => true]));

        return new ContentTypePromise($contentType);
    }

    /**
     * @param LinkInterface $link
     * @param array         $options
     * @param string|null   $locale
     * @return PromiseInterface
     */
    public function resolveLink($link, array $options = [], $locale = null)
    {
        //check whether the "link" is already actually a resolved resource
        if ($link instanceof ResourceInterface) {
            return promise_for($link);
        }
        try {
            switch ($link->getLinkType()) {
                case 'Entry':
                    /** @var PromiseInterface $entry */
                    $entry = $this->getEntry(
                        $link->getId(),
                        $link->getSpaceName(),
                        array_merge($options, ['async' => true]),
                        $locale
                    );

                    return $entry;
                case 'Asset':
                    /** @var PromiseInterface $asset */
                    $asset = $this->getAsset(
                        $link->getId(),
                        $link->getSpaceName(),
                        array_merge($options, ['async' => true]),
                        $locale
                    );

                    return $asset;
                case 'ContentType':
                    /** @var PromiseInterface $contentType */
                    $contentType = $this->getContentType(
                        $link->getId(),
                        $link->getSpaceName(),
                        array_merge($options, ['async' => true])
                    );

                    return $contentType;
                default:
                    throw new \InvalidArgumentException(
                        sprintf('Tried to resolve unknown link type "%s".', $link->getLinkType())
                    );
            }
        } catch (ResourceUnavailableException $e) {
            throw new LinkUnresolvableException($link, null, 0, $e);
        }
    }

    /**
     * Flushes the whole cache. Returns true if flush was successful, false otherwise.
     *
     * @return bool
     */
    public function flushCache($spaceName)
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
     * @param array                $options
     * @return ResourceInterface|EntryInterface|AssetInterface|ContentTypeInterface|SpaceInterface|ResourceArray|PromiseInterface
     */
    private function doRequest(
        $spaceData,
        $spaceName,
        $endpointUrl,
        $exceptionMessage,
        $api,
        $queryType,
        $cacheDisambiguator = '',
        array $parameters = [],
        array $options = []
    ) {
        $promise = coroutine(
            function () use ($spaceData, $spaceName, $endpointUrl, $exceptionMessage, $api, $queryType, $cacheDisambiguator, $parameters, $options) {
                $options = $this->mergeOptions($options);
                $shouldBuildTypedResources = !$options['untyped'];
                $test = $options['test'];
                //ensure parameters are complete first of all as cache keys are generated from them
                $parameters = $this->completeParameters($parameters, $spaceName);
                //only use cache if this is a Content Delivery API request
                $cacheKey = $this->generateCacheKey($spaceData['key'], $queryType, $api === self::PREVIEW_API, $cacheDisambiguator, $parameters);
                $readCache = $this->ensureCache($spaceData['cache'], $options['fresh_fetch']);
                $writeCache = $this->ensureCache($spaceData['cache']);
                $readCacheItem = $readCache->getItem($cacheKey);
                $writeCacheItem = $writeCache->getItem($cacheKey);
                $readFallbackCache = $this->ensureCache($spaceData['fallback_cache'], $options['fresh_fetch']);
                $writeFallbackCache = $this->ensureCache($spaceData['fallback_cache']);
                $getItemFromCache = function (CacheItemPoolInterface $pool) use ($cacheKey) {
                    return $pool->getItem($cacheKey);
                };
                $assetDecorator = $this->ensureAssetDecorator($spaceData['asset_decorator']);
                /**
                 * Returns a built response if it passes test, or null if it doesn't.
                 *
                 * @param string $json
                 * @return PromiseInterface
                 */
                $buildResponseFromJson = function ($json) use ($spaceData, $assetDecorator, $shouldBuildTypedResources, $test) {
                    $json = (is_array($json)) ? $json : json_decode($json, true);
                    if (null === $json) {
                        return null;
                    }

                    return $this->buildResponseFromRaw(
                        $json,
                        $spaceData['name'],
                        $assetDecorator,
                        $shouldBuildTypedResources
                    )->then(
                        function ($builtResponse) use ($test) {
                            return (call_user_func($test, $builtResponse)) ? $builtResponse : null;
                        }
                    );
                };
                if ($readCacheItem->isHit()) {
                    $cacheItemJson = $readCacheItem->get();
                    if (is_string($cacheItemJson) && strlen($cacheItemJson) > 0) {
                        $cacheItemData = json_decode($cacheItemJson, true);
                        //if we are caching fail responses, and this cache item has null content, it's a fail
                        if (null !== $cacheItemData) {
                            $builtResponse = (yield $buildResponseFromJson($cacheItemJson));
                            if ($builtResponse) {
                                yield promise_for($builtResponse);
                                return;
                            }
                        }
                        if ($this->cacheFailResponses) {
                            /**
                             * @var CacheItemInterface $fallbackCacheItem
                             */
                            $fallbackCacheItem = $getItemFromCache($readFallbackCache);
                            if ($api === self::CONTENT_DELIVERY_API && $fallbackCacheItem->isHit()) {
                                $fallbackJson = $fallbackCacheItem->get();
                                if (is_string($fallbackJson) && $fallbackJson !== json_encode(null) && strlen($fallbackJson) > 0) {
                                    $builtResponse = (yield $buildResponseFromJson($fallbackJson));
                                    if ($builtResponse) {
                                        yield promise_for($builtResponse);
                                        return;
                                    }
                                }
                            }
                            throw new ResourceUnavailableException(null, sprintf('Fetched fail response from cache for key "%s".', $cacheKey));
                        }
                    }
                }
                $request = $this->createRequest($endpointUrl, 'GET');
                $request = $this->setAuthHeaderOnRequest($request, $spaceData['access_token']);
                $request = $this->setApiVersionHeaderOnRequest($request);

                $queryParams = [];
                //set the include level
                if (null !== $options['include_level']) {
                    $queryParams['include'] = $options['include_level'];
                }
                //set parameters onto the request
                foreach ($parameters as $param) {
                    /**
                     * @var ParameterInterface $param
                     */
                    $queryParams[$param->getKey()] = $param->getValue();
                }

                $unavailableException = null;
                $response = null;
                $didFetch = false;
                $timer = $this->logger->getStartedTimer();

                $log = function ($description, $responseBody) use ($timer, $queryType, $api) {
                    //fake data for now
                    $analysis = (new ResponseAnalyzer())->analyze($responseBody);
                    $this->logger->log(
                        $description,
                        $timer,
                        $this->getLogResourceTypeForQueryType($queryType),
                        $api,
                        $analysis->getIndicatedResponseCount(),
                        $analysis->indicatesError()
                    );
                };

                try {
                    /** @var Response $response */
                    $response = ($shouldBuildTypedResources)
                        ? array_values((yield all([
                            $this->sendRequestWithQueryParams($request, $queryParams),
                            $this->ensureContentTypesLoaded($spaceName)
                        ])))[0]
                        : (yield $this->sendRequestWithQueryParams($request, $queryParams));
                    $didFetch = true;
                } catch (RequestException $e) {
                    /**
                     * @var CacheItemInterface $fallbackCacheItem
                     */
                    $fallbackCacheItem = $getItemFromCache($writeFallbackCache);
                    if (in_array($api, [self::CONTENT_DELIVERY_API, self::PREVIEW_API]) && $fallbackCacheItem->isHit()) {
                        $fallbackJson = $fallbackCacheItem->get();
                        if (is_string($fallbackJson) && $fallbackJson !== json_encode(null) && strlen($fallbackJson) > 0) {
                            //save fallback value into main cache
                            $writeCacheItem->set($fallbackJson);
                            $writeCache->save($writeCacheItem);

                            yield $this->buildResponseFromRaw(
                                json_decode($fallbackJson, true),
                                $spaceData['name'],
                                $assetDecorator,
                                $shouldBuildTypedResources
                            );
                            return;
                        }
                    }
                    //if there is a rate limit error, wait (if applicable)
                    /** @var Response|null $exceptionResponse */
                    $exceptionResponse = ($e->hasResponse()) ? $e->getResponse() : null;
                    if ($exceptionResponse && $exceptionResponse->getStatusCode() === 429 && $spaceData['retry_time_after_rate_limit_in_ms']) {
                        usleep(intval($spaceData['retry_time_after_rate_limit_in_ms']));

                        yield $this->doRequest(
                            $spaceData,
                            $spaceName,
                            $endpointUrl,
                            $exceptionMessage,
                            $api,
                            $queryType,
                            $cacheDisambiguator,
                            $parameters,
                            array_merge($options, ['async' => true])
                        );
                        return;
                    }
                    $unavailableException = new ResourceUnavailableException($exceptionResponse, $exceptionMessage, 0, $e);
                } finally {
                    if ($didFetch) {
                        $log(
                            sprintf(
                                'Fetched a fresh response from URL "%s".',
                                $this->getUriForRequest($request, $queryParams)
                            ),
                            (isset($response)) ? strval($response->getBody()) : ''
                        );
                    }
                }
                if (!$unavailableException && $response && $response->getStatusCode() != '200') {
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
                //build the response so we can check it is valid
                $responseJson = json_encode(
                    (!$unavailableException && $response) ? $this->responseAsArrayFromJson($response) : null
                );
                $builtResponse = ($responseJson) ? (yield $buildResponseFromJson($responseJson)) : null;
                $isValidResponse = (bool) $builtResponse;

                //save into cache
                $isSuccessResponseData = !$unavailableException && $isValidResponse;

                if ($isSuccessResponseData || $this->cacheFailResponses) {
                    $writeCacheItem->set($responseJson);
                    $writeCache->save($writeCacheItem);
                }

                if (!isset($fallbackCacheItem)) {
                    /**
                     * @var CacheItemInterface $fallbackCacheItem
                     */
                    $fallbackCacheItem = $getItemFromCache($writeFallbackCache);
                }
                if ((!$unavailableException || $fallbackCacheItem->get() === null) && $isSuccessResponseData) {
                    $fallbackCacheItem->set($responseJson);
                    $writeFallbackCache->save($fallbackCacheItem);
                }
                if ($unavailableException instanceof \Exception) {
                    throw $unavailableException;
                }
                //if built response did not pass provided test
                if (!$builtResponse) {
                    //try to load a valid response from fallback cache
                    /**
                     * @var CacheItemInterface $fallbackCacheItem
                     */
                    $fallbackCacheItem = $getItemFromCache($readFallbackCache);
                    if ($api === self::CONTENT_DELIVERY_API && $fallbackCacheItem->isHit()) {
                        $fallbackJson = $fallbackCacheItem->get();
                        if (is_string($fallbackJson) && $fallbackJson !== json_encode(null) && strlen($fallbackJson) > 0) {
                            $builtResponse = (yield $buildResponseFromJson($fallbackJson));
                            if ($builtResponse) {
                                yield promise_for($builtResponse);
                                return;
                            }
                        }
                    }
                    throw new ResourceUnavailableException(
                        $response,
                        'Contentful returned a valid response but it did not pass the provided test'
                    );
                }

                yield promise_for($builtResponse);
            }
        );

        return (isset($options['async']) && $options['async']) ? $promise : $promise->wait();
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
            return '';
        }

        return $map[$queryType];
    }

    private function getSpaceDataForName($spaceName)
    {
        $defaultData = [
            'cache' => null,
            'fallback_cache' => null,
            'preview_mode' => false,
            'retry_time_after_rate_limit_in_ms' => 750,
            'asset_decorator' => null,
        ];
        if (!array_key_exists($spaceName, $this->spaces)) {
            throw new \InvalidArgumentException(sprintf('The space with name "%s" is not known to this client.', $spaceName));
        }

        return array_merge($defaultData, $this->spaces[$spaceName], ['name' => $spaceName]);
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
     * @param \GuzzleHttp\Psr7\Request $request
     * @param string                                    $accessToken
     * @return \GuzzleHttp\Psr7\Request
     */
    private function setAuthHeaderOnRequest($request, $accessToken)
    {
        return $this->setHeaderOnRequest(
            $request,
            'Authorization',
            sprintf('Bearer %s', $accessToken)
        );
    }

    /**
     * @param \GuzzleHttp\Psr7\Request $request
     * @return \GuzzleHttp\Psr7\Request
     */
    private function setApiVersionHeaderOnRequest($request)
    {
        //specify version 1 header
        return $this->setHeaderOnRequest(
            $request,
            'Content-Type',
            sprintf(
                'application/vnd.contentful.%s.v1+json',
                'delivery'
            )
        );
    }

    /**
     * @param array $data
     * @param string $spaceName
     * @param AssetDecoratorInterface|null $assetDecorator
     * @param bool $useTypedResources
     * @return PromiseInterface
     */
    private function buildResponseFromRaw(
        array $data,
        $spaceName,
        AssetDecoratorInterface $assetDecorator = null,
        $useTypedResources = true
    ) {
        static $resourceBuilder;
        if (empty($resourceBuilder)) {
            $resourceBuilder = new ResourceBuilder($this->resourcePool);
            $resourceBuilder->setResolveLinkFunction($this->createResolveLinkFunction());
        }
        $resourceBuilder->setUseDynamicEntries($useTypedResources);

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
            'fresh_fetch' => false,
            'untyped' => !$this->useDynamicEntries,
        ];

        $mergedOptions = array_merge($defaultOptions, $options);
        $mergedOptions['test'] = (isset($options['test']) && is_callable($options['test']))
            ? $options['test']
            : function ($builtResponse) {
                return true;
            };

        return $mergedOptions;
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
            $key .= '↦' . $disambiguator;
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
                if ($param instanceof DecidesCacheKeyInterface) {
                    return $param->getCacheKey();
                }

                return sprintf('|%s|%s↦%s', $param->getName(), $param->getKey(), $param->getValue());
            }, $parameters));
        }
        $illegalPsr6Characters = '{}()/\@:';

        return strtr($key, array_fill_keys(str_split($illegalPsr6Characters), ''));
    }

    /**
     * Ensures a cache by passing through a passed in cache, or returning a null cache if arg is not a cache.
     *
     * @param mixed $candidate
     * @param bool  $forceNull Whether to force a null cache.
     * @return CacheItemPoolInterface
     */
    private function ensureCache($candidate, $forceNull = false)
    {
        if ($forceNull || !$candidate instanceof CacheItemPoolInterface) {
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
    private function completeParameter(ParameterInterface $parameter, $spaceName)
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
     * @param IncompleteParameterInterface $filter
     * @param string                       $spaceName
     * @return ParameterInterface|null
     */
    private function resolveContentTypeNameFilter(IncompleteParameterInterface $filter, $spaceName)
    {
        static $contentTypeFilterProvider;
        if (empty($contentTypeFilterProvider)) {
            $contentTypeFilterProvider = new ContentTypeFilterProvider($this);
        }

        return $contentTypeFilterProvider->createForContentTypeName($filter->getValue(), $spaceName);
    }

    /**
     * @param string $spaceName
     * @return PromiseInterface
     */
    private function ensureContentTypesLoaded($spaceName)
    {
        /** @var PromiseInterface $contentTypes */
        $contentTypes = $this->getContentTypes([], $spaceName, ['async' => true, 'untyped' => true]);

        return $contentTypes
            ->then(
                function ($types) use ($spaceName) {
                    $this->findEnvelopeForSpace($spaceName)->insertAllContentTypes($types);

                    return $types;
                }
            );
    }

    /**
     * @param array $options
     * @return bool
     */
    private function isAsyncCall(array $options)
    {
        return isset($options['async']) && true === $options['async'];
    }

    private function findEnvelopeForSpace($space)
    {
        $spaceName = ($space instanceof SpaceInterface) ? $space->getName() : $space;

        return $this->resourcePool->getEnvelopeForSpace($spaceName);
    }

    private function createResolveLinkFunction(): callable
    {
        return function ($link, $locale = null) {
            if ($link instanceof LinkInterface) {
                $this->linkResolveCounter->logLink($link);
            }

            return $this->resolveLink($link, [], $locale);
        };
    }

    private function setResolveLinkFunctionOntoEnvelope(ResourceEnvelopeInterface $envelope)
    {
        if ($envelope instanceof CanResolveResourcesInterface) {
            $envelope->setResolveLinkFunction($this->createResolveLinkFunction());
        }
    }
}
