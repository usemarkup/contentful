<?php

namespace Markup\Contentful;

interface WebhookInterface extends ResourceInterface
{
    /**
     * Returns the URL that the webhook will hit.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Gets the HTTP Basic username being used to hit the webhook, if this is being used.
     *
     * @return string|null
     */
    public function getHttpBasicUsername();

    /**
     * Gets the HTTP Basic password being used to hit the webhook, if this is being used.
     *
     * @return string|null
     */
    public function getHttpBasicPassword();

    /**
     * Gets whether HTTP Basic is used for this webhook.
     *
     * @return bool
     */
    public function usesHttpBasic();
} 
