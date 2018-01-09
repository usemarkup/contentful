<?php

namespace Markup\Contentful\Exception;

use GuzzleHttp\Psr7\Response;

class ResourceUnavailableException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var Response|null
     */
    private $response;

    public function __construct(Response $response = null, ...$args)
    {
        $this->response = $response;
        parent::__construct(...$args);
    }

    /**
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
