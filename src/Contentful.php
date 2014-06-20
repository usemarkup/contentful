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
     * @param array $spaces A list of known spaces keyed by an arbitrary name. The space array must be a hash with 'key', 'access_token' and, optionally, an 'api_domain' value.
     * @param array $options A set of options, including 'guzzle_adapter' (a Guzzle adapter object), and 'guzzle_event_subscribers' (a list of Guzzle event subscribers to attach)
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
    }

    /**
     * @param string $spaceName
     * @return SpaceInterface
     * @throws Exception\ResourceUnavailableException
     */
    public function getSpace($spaceName = null)
    {
        $spaceData = $this->getSpaceDataForName($spaceName);
        $request = $this->guzzle->createRequest('GET', $this->getEndpointUrl(sprintf('/spaces/%s', $spaceData['key']), self::CONTENT_DELIVERY_API));
        $this->setAuthHeaderOnRequest($request, $spaceData['access_token']);

        try {
            $response = $this->guzzle->send($request);
        } catch (RequestException $e) {
            throw new ResourceUnavailableException($e->getResponse(), sprintf('The space "%s" was unavailable.', $spaceName), 0, $e);
        }
        if ($response->getStatusCode() !== '200') {
            throw new ResourceUnavailableException(
                $response,
                sprintf(
                    'The space "%s" was not available. Contentful returned a "%s - %s" response.',
                    $spaceName,
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
        return (self::CONTENT_MANAGEMENT_API) ? 'api.contentful.com' : 'cdn.contentful.com';
    }

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

    private function buildResponseFromRaw(array $data)
    {
        static $resourceBuilder;
        if (empty($resourceBuilder)) {
            $resourceBuilder = new ResourceBuilder();
        }

        return $resourceBuilder->buildFromData($data);
    }
}
