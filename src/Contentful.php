<?php

namespace Markup\Contentful;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Markup\Contentful\Cache\NullCacheItemPool;
use Markup\Contentful\Decorator\AssetDecoratorInterface;
use Markup\Contentful\Decorator\NullAssetDecorator;
use Markup\Contentful\Exception\LinkUnresolvableException;
use Markup\Contentful\Exception\ResourceUnavailableException;
use Markup\Contentful\Filter\ContentTypeFilterProvider;
use Markup\Contentful\Filter\ContentTypeNameFilter;
use Markup\Contentful\Log\LoggerInterface;
use Markup\Contentful\Log\LogInterface;
use Markup\Contentful\Log\NullLogger;
use Markup\Contentful\Log\StandardLogger;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Contentful
{
    use GuzzleAbstractionTrait;

    const CONTENT_DELIVERY_API = 'cda';
    const CONTENT_MANAGEMENT_API = 'cma';
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
     * @param array $options A set of options, including:
     *                      'guzzle_handler' (a Guzzle handler object)
     *                      'guzzle_timeout' (a number of seconds to set as the timeout for lookups using Guzzle)
     *                      'guzzle_proxy' (defines a HTTP Proxy URL which will is used for requesting the Contentful API)
     *                      'include_level' (the levels of linked content to include in responses by default)
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
        $this->envelope = new ResourceEnvelope();
    }

    /**
     * @param string|SpaceInterface $space The space name, or space object.
     * @param array $options
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
     * @param string|SpaceInterface $space
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
     * @param string               $space
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
     * @param array                 $options
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
     * @param array                 $options
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

        if ($options) {
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
        //fetch them all and pick one out, as it is likely we'll want to access others
        $contentTypes = $this->getContentTypes([], $space);
        foreach ($contentTypes as $contentType) {
            $this->envelope->insertContentType($contentType);
        }

        return $this->envelope->findContentType($id);
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
        $contentTypeFromEnvelope = $this->envelope->findContentTypeByName($name);
        if ($contentTypeFromEnvelope) {
            return $contentTypeFromEnvelope;
        }
        $contentTypes = $this->getContentTypes([], $space, $options);
        $foundContentType = null;
        foreach ($contentTypes as $contentType) {
            if ($contentType->getName() === $name) {
                $foundContentType = $contentType;
            }
            $this->envelope->insertContentType($contentType);
        }

        return $foundContentType;
    }

    /**
     * @param Link $link
     * @param array $options
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
            throw new LinkUnresolvableException($link, null, 0, $e);
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
     * @param array                $options
     * @return ResourceInterface|EntryInterface|AssetInterface|ContentTypeInterface|SpaceInterface|ResourceArray
     */
    private function doRequest(
        $spaceData,
        $spaceName,
        $endpointUrl,
        $exceptionMessage,
        $api,
        $queryType = null,
        $cacheDisambiguator = '',
        array $parameters = [],
        array $options = []
    ) {
        $timer = $this->logger->getStartedTimer();
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
         * @return ResourceInterface|ResourceArray|null
         */
        $buildResponseFromJson = function ($json) use ($spaceData, $assetDecorator, $shouldBuildTypedResources, $test) {
            $json = (is_array($json)) ? $json : json_decode($json, true);
            if (null === $json) {
                return null;
            }
            $builtResponse = $this->buildResponseFromRaw(
                $json,
                $spaceData['name'],
                $assetDecorator,
                $shouldBuildTypedResources
            );

            return (call_user_func($test, $builtResponse)) ? $builtResponse : null;
        };
        $log = function ($description, $isCacheHit, $type) use ($timer, $queryType, $api) {
            $this->logger->log(
                $description,
                $isCacheHit,
                $timer,
                $type,
                $this->getLogResourceTypeForQueryType($queryType),
                $api
            );
        };
        if ($api !== self::CONTENT_MANAGEMENT_API && $readCacheItem->isHit()) {
            $cacheItemJson = $readCacheItem->get();
            if (is_string($cacheItemJson) && strlen($cacheItemJson) > 0) {
                $cacheItemData = json_decode($cacheItemJson, $assoc = true);
                //if we are caching fail responses, and this cache item has null content, it's a fail
                if (null !== $cacheItemData) {
                    $log(
                        sprintf('Fetched response from cache for key "%s".', $cacheKey),
                        true,
                        LogInterface::TYPE_RESPONSE
                    );

                    $builtResponse = $buildResponseFromJson($cacheItemJson);
                    if ($builtResponse) {
                        return $builtResponse;
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
                            $log(
                                sprintf('Used successful fallback cache value as main cache has a fail response for key "%s".', $cacheKey),
                                true,
                                LogInterface::TYPE_RESOURCE
                            );
                            $builtResponse = $buildResponseFromJson($fallbackJson);
                            if ($builtResponse) {
                                return $builtResponse;
                            }
                        }
                    }
                    throw new ResourceUnavailableException(null, sprintf('Fetched fail response from cache for key "%s".', $cacheKey));
                }
            }
        }
        $request = $this->createRequest($endpointUrl, 'GET');
        $request = $this->setAuthHeaderOnRequest($request, $spaceData['access_token']);
        $request = $this->setApiVersionHeaderOnRequest($request, $api);

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
        try {
            /**
             * @var ResponseInterface|Response $response
             */
            $response = $this->sendRequestWithQueryParams($request, $queryParams);
        } catch (RequestException $e) {
            /**
             * @var CacheItemInterface $fallbackCacheItem
             */
            $fallbackCacheItem = $getItemFromCache($writeFallbackCache);
            if (in_array($api, [self::CONTENT_DELIVERY_API, self::PREVIEW_API]) && $fallbackCacheItem->isHit()) {
                $fallbackJson = $fallbackCacheItem->get();
                if (is_string($fallbackJson) && $fallbackJson !== json_encode(null) && strlen($fallbackJson) > 0) {
                    $log(
                        sprintf('Fetched response from fallback cache for key "%s".', $cacheKey),
                        true,
                        LogInterface::TYPE_RESOURCE
                    );
                    //save fallback value into main cache
                    $writeCacheItem->set($fallbackJson);
                    $writeCache->save($writeCacheItem);

                    return $this->buildResponseFromRaw(
                        json_decode($fallbackJson, $assoc = true),
                        $spaceData['name'],
                        $assetDecorator,
                        $shouldBuildTypedResources
                    );
                }
            }
            //if there is a rate limit error, wait (if applicable)
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 429 && $spaceData['retry_time_after_rate_limit_in_ms']) {
                usleep(intval($spaceData['retry_time_after_rate_limit_in_ms']));

                return $this->doRequest(
                    $spaceData,
                    $spaceName,
                    $endpointUrl,
                    $exceptionMessage,
                    $api,
                    $queryType,
                    $cacheDisambiguator,
                    $parameters,
                    $options
                );
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
        //build the response so we can check it is valid
        $responseJson = json_encode(
            (!$unavailableException) ? $this->responseAsArrayFromJson($response) : null
        );
        $builtResponse = ($responseJson) ? $buildResponseFromJson($responseJson) : null;
        $isValidResponse = (bool) $builtResponse;

        //save into cache
        if ($api !== self::CONTENT_MANAGEMENT_API) {
            $isSuccessResponseData = !$unavailableException && $isValidResponse;
            $writeCacheItem->set($responseJson);
            $writeCache->save($writeCacheItem);
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
        }
        if ($unavailableException instanceof \Exception) {
            throw $unavailableException;
        }
        //if built response did not pass provided test
        if (!$builtResponse) {
            $log(
                sprintf(
                    'Fetched a fresh response from URL "%s" that did not pass provided test',
                    $this->getUriForRequest($request)
                ),
                false,
                LogInterface::TYPE_RESOURCE
            );
            //try to load a valid response from fallback cache
            /**
             * @var CacheItemInterface $fallbackCacheItem
             */
            $fallbackCacheItem = $getItemFromCache($readFallbackCache);
            if ($api === self::CONTENT_DELIVERY_API && $fallbackCacheItem->isHit()) {
                $fallbackJson = $fallbackCacheItem->get();
                if (is_string($fallbackJson) && $fallbackJson !== json_encode(null) && strlen($fallbackJson) > 0) {
                    $log(
                        sprintf('Used successful fallback cache value as fresh fetch for key "%s" did not pass provided test.', $cacheKey),
                        true,
                        LogInterface::TYPE_RESOURCE
                    );
                    $builtResponse = $buildResponseFromJson($fallbackJson);
                    if ($builtResponse) {
                        return $builtResponse;
                    }
                }
            }
            throw new ResourceUnavailableException(
                $response,
                'Contentful returned a valid response but it did not pass the provided test'
            );
        }
        $log(
            sprintf(
                'Fetched a fresh response from URL "%s".',
                $this->getUriForRequest($request)
            ),
            false,
            LogInterface::TYPE_RESPONSE
        );

        return $builtResponse;
    }

    /**
     * @param string $queryType
     * @return string|null
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
     * @param string                                    $api
     * @return \GuzzleHttp\Psr7\Request
     */
    private function setApiVersionHeaderOnRequest($request, $api)
    {
        //specify version 1 header
        return $this->setHeaderOnRequest(
            $request,
            'Content-Type',
            sprintf(
                'application/vnd.contentful.%s.v1+json',
                ($api === self::CONTENT_MANAGEMENT_API) ? 'management' : 'delivery'
            )
        );
    }

    /**
     * @param array $data
     * @param string $spaceName
     * @param AssetDecoratorInterface $assetDecorator
     * @param bool $useTypedResources
     * @return ResourceInterface
     */
    private function buildResponseFromRaw(
        array $data,
        $spaceName = null,
        AssetDecoratorInterface $assetDecorator = null,
        $useTypedResources = true
    ) {
        static $resourceBuilder;
        if (empty($resourceBuilder)) {
            $resourceBuilder = new ResourceBuilder($this->envelope);
            $resourceBuilder->setResolveLinkFunction(function ($link) {
                return $this->resolveLink($link);
            });
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
     * @param IncompleteParameterInterface $filter
     * @param string                       $spaceName
     * @return ParameterInterface
     */
    private function resolveContentTypeNameFilter(IncompleteParameterInterface $filter, $spaceName = null)
    {
        static $contentTypeFilterProvider;
        if (empty($contentTypeFilterProvider)) {
            $contentTypeFilterProvider = new ContentTypeFilterProvider($this);
        }

        return $contentTypeFilterProvider->createForContentTypeName($filter->getValue(), $spaceName);
    }
}
