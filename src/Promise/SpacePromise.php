<?php

namespace Markup\Contentful\Promise;

use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\Locale;
use Markup\Contentful\Metadata;
use Markup\Contentful\Space;
use Markup\Contentful\SpaceInterface;

class SpacePromise extends ResourcePromise implements SpaceInterface
{
    /**
     * Gets the name of the space.
     *
     * @return string
     */
    public function getName()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof SpaceInterface) {
            return '';
        }

        return $resolved->getName();
    }

    /**
     * Gets the locales associated with the space.
     *
     * @return Locale[]
     */
    public function getLocales()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof SpaceInterface) {
            return [];
        }

        return $resolved->getLocales();
    }

    /**
     * Gets the default locale.
     *
     * @return Locale
     */
    public function getDefaultLocale()
    {
        $resolved = $this->getResolved();
        if (!$resolved instanceof SpaceInterface) {
            return new Locale('en', 'English');
        }

        return $resolved->getDefaultLocale();
    }

    protected function addRejectionHandlerToPromise(PromiseInterface $promise)
    {
        return $promise
            ->otherwise(function ($reason) {
                return new Space(null, new Metadata(), []);
            });
    }
}
