<?php

namespace Markup\Contentful;

class Webhook implements WebhookInterface
{
    use MetadataAccessTrait;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $httpBasicUsername;

    /**
     * @var string
     */
    private $httpBasicPassword;

    /**
     * @param string $url
     * @param MetadataInterface $metadata
     * @param string $httpBasicUsername
     * @param string $httpBasicPassword
     */
    public function __construct($url, MetadataInterface $metadata, $httpBasicUsername = null, $httpBasicPassword = null)
    {
        $this->url = $url;
        $this->metadata = $metadata;
        $this->httpBasicUsername = $httpBasicUsername;
        $this->httpBasicPassword = $httpBasicPassword;
    }

    /**
     * Returns the URL that the webhook will hit.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets the HTTP Basic username being used to hit the webhook, if this is being used.
     *
     * @return string|null
     */
    public function getHttpBasicUsername()
    {
        return $this->httpBasicUsername;
    }

    /**
     * Gets the HTTP Basic password being used to hit the webhook, if this is being used.
     *
     * @return string|null
     */
    public function getHttpBasicPassword()
    {
        return $this->httpBasicPassword;
    }

    /**
     * Gets whether HTTP Basic is used for this webhook.
     *
     * @return bool
     */
    public function usesHttpBasic()
    {
        return $this->httpBasicUsername && $this->httpBasicPassword;
    }
}
