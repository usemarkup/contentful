<?php

namespace Markup\Contentful;

use GuzzleHttp\Adapter\AdapterInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use Markup\Contentful\Exception\ResourceUnavailableException;

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
     * @var int
     */
    private $defaultIncludeLevel;

    /**
     * @param array $spaces A list of known spaces keyed by an arbitrary name. The space array must be a hash with 'key', 'access_token' and, optionally, an 'api_domain' value.
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
            default:
                throw new \InvalidArgumentException(sprintf('Tried to resolve unknown link type "%s".', $link->getLinkType()));
        }
    }

    /**
     * @param array  $spaceData
     * @param string $endpointUrl
     * @param string $exceptionMessage
     * @param string $api
     * @return ResourceInterface
     */
    private function doRequest($spaceData, $endpointUrl, $exceptionMessage, $api, array $options)
    {
        $options = $this->mergeOptions($options);
        $request = $this->guzzle->createRequest('GET', $endpointUrl);
        $this->setAuthHeaderOnRequest($request, $spaceData['access_token']);
        $this->setApiVersionHeaderOnRequest($request, $api);
        //set the include level
        if (null !== $options['include_level']) {
            $request->getQuery()->set('include', $options['include_level']);
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

        return $this->buildResponseFromRaw($response->json());
    }

    private function getSpaceDataForName($spaceName = null)
    {
        if ($spaceName) {
            if (!array_key_exists($spaceName, $this->spaces)) {
                throw new \InvalidArgumentException(sprintf('The space with name "%s" is not known to this client.', $spaceName));
            }

            return $this->spaces[$spaceName];
        }

        return array_values($this->spaces)[0];
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
}
