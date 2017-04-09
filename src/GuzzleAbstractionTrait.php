<?php

namespace Markup\Contentful;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * A trait providing methods for abstracted use of the Guzzle library.
 */
trait GuzzleAbstractionTrait
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @param Request $request
     * @param array                                     $queryParams
     * @return ResponseInterface
     */
    private function sendRequestWithQueryParams(Request $request, array $queryParams = [])
    {
        return $this->guzzle->send($request, [RequestOptions::QUERY => $queryParams]);
    }

    /**
     * @param string $uri
     * @param string $method
     * @return Request
     */
    private function createRequest($uri, $method)
    {
        return new Request($method, $uri);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getUriForRequest(Request $request)
    {
        return strval($request->getUri());
    }

    /**
     * @param Request $request
     * @param string     $header
     * @param string     $value
     * @return Request
     */
    private function setHeaderOnRequest(Request $request, $header, $value)
    {
        return $request->withHeader($header, $value);
    }

    /**
     * @param \GuzzleHttp\Psr7\Response $response
     * @return array
     */
    private function responseAsArrayFromJson($response)
    {
        return json_decode(strval($response->getBody()), true);
    }
}
