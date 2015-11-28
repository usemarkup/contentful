<?php

namespace Markup\Contentful;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * A trait providing methods for abstracted use of the Guzzle library, versions 5 and 6.
 *
 * Assumes traited class has an instance variable called $guzzle containing a Guzzle client instance.
 */
trait GuzzleAbstractionTrait
{
    /**
     * @param RequestInterface|Psr7Request $request
     * @param array                                     $queryParams
     * @return \GuzzleHttp\Message\ResponseInterface|ResponseInterface
     */
    private function sendRequestWithQueryParams($request, array $queryParams = [])
    {
        if ($this->isUsingAtLeastGuzzle6()) {
            return $this->guzzle->send($request, [RequestOptions::QUERY => $queryParams]);
        } else {
            /**
             * @var RequestInterface $request
             */
            $query = $request->getQuery();
            foreach ($queryParams as $queryKey => $value) {
                $query->set($queryKey, $value);
            }
            return $this->guzzle->send($request);
        }
    }

    /**
     * @param string $uri
     * @param string $method
     * @return Request|PsrRequest
     */
    private function createRequest($uri, $method)
    {
        if ($this->isUsingAtLeastGuzzle6()) {
            return new PsrRequest($method, $uri);
        }

        return $this->guzzle->createRequest($method, $uri);
    }

    /**
     * @param RequestInterface|\GuzzleHttp\Psr7\Request $request
     * @return string
     */
    private function getUriForRequest($request)
    {
        if ($request instanceof PsrRequest) {
            return strval($request->getUri());
        }

        /**
         * @var RequestInterface $request
         */
        return $request->getUrl();
    }

    /**
     * @param RequestInterface|PsrRequest $request
     * @param string                                    $header
     * @param string                                    $value
     * @return RequestInterface|PsrRequest
     */
    private function setHeaderOnRequest($request, $header, $value)
    {
        if ($request instanceof PsrRequest) {
            $requestWithHeader = $request->withHeader($header, $value);
        } else {
            $request->setHeader($header, $value);
            $requestWithHeader = $request;
        }

        return $requestWithHeader;
    }

    /**
     * @param ResponseInterface|\GuzzleHttp\Psr7\Response $response
     * @return array
     */
    private function responseAsArrayFromJson($response)
    {
        if (method_exists($response, 'json')) {
            return $response->json();
        }

        return json_decode(strval($response->getBody()), true);
    }

    private function isUsingAtLeastGuzzle6()
    {
        return version_compare(ClientInterface::VERSION, '6.0.0', '>=');
    }
}
