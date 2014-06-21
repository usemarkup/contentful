<?php

namespace Markup\Contentful\Exception;

use GuzzleHttp\Message\ResponseInterface;

class ResourceUnavailableException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param ResponseInterface $response
     * @param string            $message
     * @param int               $code
     * @param \Exception        $previous
     */
    public function __construct(ResponseInterface $response = null, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \GuzzleHttp\Message\ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
