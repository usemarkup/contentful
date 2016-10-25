<?php

namespace Markup\Contentful;

use GuzzleHttp\Adapter\AdapterInterface;
use GuzzleHttp\ClientInterface;

class GuzzleOptions
{
    /** @var AdapterInterface */
    private $adapter;

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

        if (self::hasGuzzleFourAdapter($options)) {
            $instance->setAdapter($options['guzzle_adapter']);
        }

        if ((isset($options['guzzle_handler'])) && (is_callable($options['guzzle_handler']))) {
            $instance->setHandler($options['guzzle_handler']);
        }

        if (isset($options['guzzle_timeout']) && (intval($options['guzzle_timeout']) > 0)) {
            $instance->defaults['timeout'] = intval($options['guzzle_timeout']);
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
     * @param array $options
     * @return bool
     */
    private static function hasGuzzleFourAdapter(array $options)
    {
        return (!self::isGuzzleFiveAtLeast() &&
            isset($options['guzzle_adapter']) &&
            $options['guzzle_adapter'] instanceof AdapterInterface);
    }

    /**
     * @return bool
     */
    private static function isGuzzleFiveAtLeast()
    {
        return version_compare(ClientInterface::VERSION, '5.0.0', '>=');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'defaults' => $this->getDefaults(),
            'handler' => $this->getHandler(),
            'adapter' => $this->getAdapter()
        ];
    }

    /**
     * @param \GuzzleHttp\Adapter\AdapterInterface $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return \GuzzleHttp\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
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
