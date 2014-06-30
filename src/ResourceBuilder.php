<?php

namespace Markup\Contentful;


class ResourceBuilder
{
    /**
     * @var callable
     */
    private $resolveLinkFunction;

    /**
     * @param array $data
     * @param IncludesEnvelope $includesEnvelope An envelope of already-resolved entries and assets.
     * @return mixed A Contentful resource.
     */
    public function buildFromData(array $data, IncludesEnvelope $includesEnvelope = null)
    {
        $includesEnvelope = $includesEnvelope ?: new IncludesEnvelope();
        if ($this->isArrayResourceData($data)) {
            return array_map(function ($resourceData) use ($includesEnvelope) {
                return $this->buildFromData($resourceData, $includesEnvelope);
            }, $data);
        }
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
            case 'Entry':
                $fields = [];
                foreach ($data['fields'] as $name => $fieldData) {
                    if ($this->isResourceData($fieldData)) {
                        $fields[$name] = $this->buildFromData($fieldData, $includesEnvelope);
                    } elseif ($this->isArrayResourceData($fieldData)) {
                        $fields[$name] = array_map(function ($itemData) use ($includesEnvelope) {
                            return $this->buildFromData($itemData, $includesEnvelope);
                        }, $fieldData);
                    } else {
                        $fields[$name] = $fieldData;
                    }
                }
                $entry = new Entry($fields, $metadata);
                if (null !== $this->resolveLinkFunction) {
                    $entry->setResolveLinkFunction($this->resolveLinkFunction);
                }

                return $entry;
            case 'Asset':
                return new Asset(
                    $data['fields']['title'],
                    (isset($data['fields']['description'])) ? $data['fields']['description'] : '',
                    new AssetFile(
                        $data['fields']['file']['fileName'],
                        $data['fields']['file']['contentType'],
                        $data['fields']['file']['details'],
                        $data['fields']['file']['url']
                    ),
                    $metadata
                );
            case 'ContentType':
                $buildContentTypeField = function ($fieldData) {
                    $options = [];
                    if (isset($fieldData['localized'])) {
                        $options['localized'] = $fieldData['localized'];
                    }
                    if (isset($fieldData['required'])) {
                        $options['required'] = $fieldData['required'];
                    }
                    return new ContentTypeField(
                        $fieldData['id'],
                        $fieldData['name'],
                        $fieldData['type'],
                        (isset($fieldData['items'])) ? $fieldData['items'] : [],
                        $options
                    );
                };

                return new ContentType(
                    $data['name'],
                    $data['description'],
                    array_map(function ($fieldData) use ($buildContentTypeField) {
                        return $buildContentTypeField($fieldData);
                    }, $data['fields']),
                    $metadata,
                    (isset($data['displayField'])) ? $buildContentTypeField($data['displayField']) : null
                );
            case 'Link':
                switch ($metadata->getLinkType()) {
                    case 'Entry':
                        $entry = $includesEnvelope->findEntry($metadata->getId());
                        if ($entry) {
                            return $entry;
                        }
                        break;
                    case 'Asset':
                        $asset = $includesEnvelope->findAsset($metadata->getId());
                        if ($asset) {
                            return $asset;
                        }
                        break;
                    default:
                        break;
                }

                return new Link($metadata);
            case 'Array':
                $arrayIncludesEnvelope = $this->buildIncludesEnvelope((isset($data['includes'])) ? $data['includes'] : []);

                return new ResourceArray(
                    array_map(function ($itemData) use ($arrayIncludesEnvelope) {
                        return $this->buildFromData($itemData, $arrayIncludesEnvelope);
                    }, $data['items']),
                    intval($data['total']),
                    intval($data['limit']),
                    intval($data['skip']),
                    $arrayIncludesEnvelope
                );
            default:
                break;
        }

        return null;
    }

    /**
     * @param callable $function
     */
    public function setResolveLinkFunction(callable $function)
    {
        $this->resolveLinkFunction = $function;

        return $this;
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
        if (isset($sys['linkType'])) {
            $metadata->setLinkType($sys['linkType']);
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

    /**
     * Tests whether the provided data represents a resource, or a link to a resource.
     *
     * @param array $data
     * @return bool
     */
    private function isResourceData($data)
    {
        return is_array($data) && array_key_exists('sys', $data) && isset($data['sys']['type']);
    }

    /**
     * Tests whether the provided data represents an array of resource data, or an array of links to resources.
     *
     * @param mixed $data
     * @return bool
     */
    private function isArrayResourceData($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        foreach ($data as $datum) {
            if (!$this->isResourceData($datum)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $includesData Raw data from a search response in an 'includes' node
     * @return IncludesEnvelope
     */
    private function buildIncludesEnvelope(array $includesData)
    {
        $envelope = new IncludesEnvelope();
        if (isset($includesData['Entry'])) {
            foreach ($includesData['Entry'] as $entryData) {
                $envelope->insertEntry($this->buildFromData($entryData));
            }
        }
        if (isset($includesData['Asset'])) {
            foreach ($includesData['Asset'] as $assetData) {
                $envelope->insertAsset($this->buildFromData($assetData));
            }
        }

        return $envelope;
    }
}
