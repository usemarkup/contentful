<?php

namespace Markup\Contentful\Exception;

use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

class ResourceUnavailableException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var ResponseInterface|Response
     */
    private $response;

    /**
     * @param ResponseInterface|Response $response
     * @param string                     $message
     * @param int                        $code
     * @param \Exception                 $previous
     */
    public function __construct($response = null, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \GuzzleHttp\Message\ResponseInterface|Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
