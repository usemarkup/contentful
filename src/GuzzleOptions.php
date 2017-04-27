<?php

namespace Markup\Contentful;

use GuzzleHttp\Adapter\AdapterInterface;
use GuzzleHttp\ClientInterface;

class GuzzleOptions
{
    /** @var callable */
    private $handler;

    /** @var  array */
    private $defaults;

    public function __construct()
    {
        $this->defaults = [];
    }

    /**
     * @param array $options
     * @return GuzzleOptions
     */
    public static function createForEnvironment(array $options)
    {
        $instance = new self();

        if ((isset($options['guzzle_handler'])) && (is_callable($options['guzzle_handler']))) {
            $instance->setHandler($options['guzzle_handler']);
        }

        if (isset($options['guzzle_timeout']) && (intval($options['guzzle_timeout']) > 0)) {
            $instance->defaults['timeout'] = intval($options['guzzle_timeout']);
            $instance->defaults['connection_timeout'] = intval($options['guzzle_timeout']);
        }

        if (isset($options['guzzle_connection_timeout']) && (intval($options['guzzle_connection_timeout']) > 0)) {
            $instance->defaults['connect_timeout'] = intval($options['guzzle_connection_timeout']);
        }

        if (!empty($options['guzzle_proxy'])) {
            $instance->defaults['proxy'] = $options['guzzle_proxy'];
        }

        return $instance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'defaults' => $this->getDefaults(),
            'handler' => $this->getHandler(),
        ];
    }

    /**
     * @param callable $handler
     */
    public function setHandler(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param array $defaults
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }
}
