<?php

namespace Markup\Contentful;

class Space implements SpaceInterface
{
    use MetadataAccessTrait;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var Locale[]
     */
    private $locales;

    /**
     * @var Locale
     */
    private $defaultLocale;

    /**
     * @param string|null $name
     * @param Metadata    $metadata
     * @param Locale[]    $locales
     * @param Locale      $defaultLocale
     */
    public function __construct($name, Metadata $metadata, array $locales, Locale $defaultLocale = null)
    {
        $this->name = $name;
        $this->metadata = $metadata;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale ?: array_values($locales)[0];
        $this->metadata->setSpace($this);
    }

    /**
     * Gets the name of the space.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the locales associated with the space.
     *
     * @return Locale[]
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Gets the default locale.
     *
     * @return Locale
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }
}
