<?php

namespace Markup\Contentful;

/**
 * Interface for a space in the API.
 */
interface SpaceInterface extends ResourceInterface
{
    /**
     * Gets the name of the space.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the locales associated with the space.
     *
     * @return Locale[]
     */
    public function getLocales();

    /**
     * Gets the default locale.
     *
     * @return Locale
     */
    public function getDefaultLocale();
} 
