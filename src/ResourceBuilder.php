<?php

namespace Markup\Contentful;


class ResourceBuilder
{
    /**
     * @param array $data
     * @return mixed A Contentful resource.
     */
    public function buildFromData(array $data)
    {
        $metadata = $this->buildMetadataFromSysData($data['sys']);
        if (!$metadata->getType()) {
            throw new \InvalidArgumentException('Resource data must always have a type in its system properties.');
        }
        switch ($metadata->getType()) {
            case 'Space':
                $locales = [];
                $defaultLocale = null;
                foreach ($data['locales'] as $locale) {
                    $localeObj = new Locale($locale['code'], $locale['name']);
                    if (isset($locale['default']) && $locale['default']) {
                        $defaultLocale = $localeObj;
                    }
                    $locales[] = $localeObj;
                }

                return new Space($data['name'], $metadata, $locales, $defaultLocale);
            default:
                break;
        }
    }

    /**
     * @param array $sys
     * @return Metadata
     */
    private function buildMetadataFromSysData(array $sys)
    {
        $metadata = new Metadata();
        if (isset($sys['id'])) {
            $metadata->setId($sys['id']);
        }
        if (isset($sys['type'])) {
            $metadata->setType($sys['type']);
        }
        if (isset($sys['space'])) {
            $metadata->setSpace($this->buildFromData($sys['space']));
        }
        if (isset($sys['contentType'])) {
            $metadata->setContentType($this->buildFromData($sys['contentType']));
        }
        if (isset($sys['revision'])) {
            $metadata->setRevision(intval($sys['revision']));
        }
        if (isset($sys['createdAt'])) {
            $metadata->setCreatedAt(new \DateTime($sys['createdAt']));
        }
        if (isset($sys['updatedAt'])) {
            $metadata->setUpdatedAt(new \DateTime($sys['updatedAt']));
        }

        return $metadata;
    }
} 
