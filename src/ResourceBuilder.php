<?php

namespace Markup\Contentful;

use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\coroutine;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Markup\Contentful\Decorator\AssetDecoratorInterface;
use Markup\Contentful\Decorator\NullAssetDecorator;

class ResourceBuilder
{
    /**
     * @var callable
     */
    private $resolveLinkFunction;

    /**
     * @var bool
     */
    private $useDynamicEntries;

    /**
     * @var ResourceEnvelope
     */
    private $envelope;

    /**
     * @param ResourceEnvelope|null $envelope
     */
    public function __construct(ResourceEnvelope $envelope = null)
    {
        $this->envelope = $envelope ?: new ResourceEnvelope();
    }

    /**
     * @param array                   $data      The raw data returned from the Contentful APIs.
     * @param string                  $spaceName The name being used for the space this data is from.
     * @param AssetDecoratorInterface $assetDecorator
     * @return PromiseInterface
     */
    public function buildFromData(array $data, $spaceName = null, AssetDecoratorInterface $assetDecorator = null)
    {
        return coroutine(
            function () use ($data, $spaceName, $assetDecorator) {
                $assetDecorator = $assetDecorator ?: new NullAssetDecorator();
                $buildFromData = function ($data) use ($spaceName, $assetDecorator) {
                    return $this->buildFromData($data, $spaceName, $assetDecorator);
                };
                if ($this->isArrayResourceData($data)) {
                    yield all(
                        array_map($buildFromData, $data)
                    );
                    return;
                }
                /** @var Metadata $metadata */
                $metadata = (yield $this->buildMetadataFromSysData($data['sys'], $buildFromData));
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

                        yield promise_for(new Space($data['name'], $metadata, $locales, $defaultLocale));
                        return;
                    case 'Entry':
                        $fields = [];
                        if (isset($data['fields'])) {
                            foreach ($data['fields'] as $name => $fieldData) {
                                if ($this->isResourceData($fieldData)) {
                                    $fields[$name] = (yield $buildFromData($fieldData));
                                } elseif ($this->isArrayResourceData($fieldData)) {
                                    $fields[$name] = (yield all(
                                        array_map(function ($itemData) use ($buildFromData) {
                                            return $buildFromData($itemData);
                                        }, $fieldData)
                                    ));
                                } else {
                                    $fields[$name] = $fieldData;
                                }
                            }
                        }
                        $entry = new Entry($fields, $metadata);
                        if (null !== $this->resolveLinkFunction) {
                            $entry->setResolveLinkFunction($this->resolveLinkFunction);
                        }
                        if ($this->useDynamicEntries) {
                            $contentType = $metadata->getContentType();
                            if ($contentType instanceof Link) {
                                $contentType = (yield call_user_func($this->resolveLinkFunction, $contentType));
                            }
                            $entry = new DynamicEntry($entry, $contentType);
                        }
                        $this->envelope->insert($entry);

                        yield promise_for($entry);
                        return;
                    case 'Asset':
                        $asset = new Asset(
                            (isset($data['fields']['title'])) ? $data['fields']['title'] : '',
                            (isset($data['fields']['description'])) ? $data['fields']['description'] : '',
                            new AssetFile(
                                (isset($data['fields']['file']) && $data['fields']['file']['fileName']) ? $data['fields']['file']['fileName'] : '',
                                (isset($data['fields']['file']) && $data['fields']['file']['contentType']) ? $data['fields']['file']['contentType'] : '',
                                (isset($data['fields']['file']) && isset($data['fields']['file']['details'])) ? $data['fields']['file']['details'] : [],
                                (isset($data['fields']['file'])) ? ((isset($data['fields']['file']['url'])) ? $data['fields']['file']['url'] : $data['fields']['file']['upload']) : ''
                            ),
                            $metadata
                        );
                        $asset = $assetDecorator->decorate($asset);
                        $this->envelope->insert($asset);

                        yield promise_for($asset);
                        return;
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

                        $contentType = new ContentType(
                            $data['name'],
                            (isset($data['description'])) ? $data['description'] : '',
                            array_map(function ($fieldData) use ($buildContentTypeField) {
                                return $buildContentTypeField($fieldData);
                            }, (isset($data['fields'])) ? $data['fields'] : []),
                            $metadata,
                            (isset($data['displayField'])) ? $data['displayField'] : null
                        );
                        $this->envelope->insert($contentType);

                        yield promise_for($contentType);
                        return;
                    case 'Link':
                        switch ($metadata->getLinkType()) {
                            case 'Entry':
                                $entry = $this->envelope->findEntry($metadata->getId());
                                if ($entry) {
                                    yield promise_for($entry);
                                    return;
                                }
                                break;
                            case 'Asset':
                                $asset = $this->envelope->findAsset($metadata->getId());
                                if ($asset) {
                                    yield promise_for($asset);
                                    return;
                                }
                                break;
                            default:
                                break;
                        }

                        yield promise_for(new Link($metadata, $spaceName));
                        return;
                    case 'Array':
                        yield $this->addToEnvelope(
                            (isset($data['includes'])) ? $data['includes'] : [],
                            $buildFromData
                        );

                        $resolveResourceData = all(
                            array_map(
                                function ($itemData) use ($buildFromData) {
                                     return coroutine(
                                         function () use ($itemData, $buildFromData) {
                                             $envelopeResource = $this->resolveResourceDataToEnvelopeResource($itemData);
                                             if ($envelopeResource) {
                                                 yield $envelopeResource;
                                                 return;
                                             }
                                             $resource = (yield $buildFromData($itemData));
                                             $this->envelope->insert($resource);

                                             yield $resource;
                                         }
                                     );
                                },
                                $data['items']
                            )
                        );

                        yield promise_for(new ResourceArray(
                            (yield $resolveResourceData),
                            intval($data['total']),
                            intval($data['limit']),
                            intval($data['skip']),
                            $this->envelope
                        ));
                        return;
                    default:
                        break;
                }
                return;
            }
        );
    }

    public function setResolveLinkFunction(callable $function)
    {
        $this->resolveLinkFunction = $function;

        return $this;
    }

    /**
     * @param bool $useDynamicEntries
     * @return self
     */
    public function setUseDynamicEntries($useDynamicEntries)
    {
        $this->useDynamicEntries = $useDynamicEntries;

        return $this;
    }

    /**
     * @param array $sys
     * @param callable $buildFromData
     * @return PromiseInterface
     */
    private function buildMetadataFromSysData(array $sys, callable $buildFromData)
    {
        return coroutine(
            function () use ($sys, $buildFromData) {
                $metadata = new Metadata();
                if (isset($sys['id'])) {
                    $metadata->setId($sys['id']);
                }
                if (isset($sys['type'])) {
                    $metadata->setType($sys['type']);
                }
                if (isset($sys['space'])) {
                    $metadata->setSpace((yield $buildFromData($sys['space'])));
                }
                if (isset($sys['contentType'])) {
                    $metadata->setContentType((yield $buildFromData($sys['contentType'])));
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
                if (isset($sys['locale'])) {
                    $metadata->setLocale($sys['locale']);
                }

                yield $metadata;
            }
        );
    }

    /**
     * @param array $data
     * @return ResourceInterface|null
     */
    private function resolveResourceDataToEnvelopeResource(array $data)
    {
        if (!isset($data['sys']['id']) || !isset($data['sys']['type'])) {
            return null;
        }
        switch ($data['sys']['type']) {
            case 'Entry':
                return $this->envelope->findEntry($data['sys']['id']);
                break;
            case 'Asset':
                return $this->envelope->findAsset($data['sys']['id']);
                break;
            case 'ContentType':
                return $this->envelope->findContentType($data['sys']['id']);
                break;
            default:
                return null;
        }
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
     * @param array $includesData
     * @param callable $buildFromData
     * @return PromiseInterface
     */
    private function addToEnvelope(array $includesData, callable $buildFromData)
    {
        return coroutine(
            function () use ($includesData, $buildFromData) {
                if (isset($includesData['Entry'])) {
                    foreach ($includesData['Entry'] as $entryData) {
                        if (!isset($entryData['sys']['id']) || $this->envelope->hasEntry($entryData['sys']['id'])) {
                            continue;
                        }
                        $this->envelope->insertEntry((yield $buildFromData($entryData)));
                    }
                }
                if (isset($includesData['Asset'])) {
                    foreach ($includesData['Asset'] as $assetData) {
                        if (!isset($assetData['sys']['id']) || $this->envelope->hasAsset($assetData['sys']['id'])) {
                            continue;
                        }
                        $this->envelope->insertAsset((yield $buildFromData($assetData)));
                    }
                }

                yield promise_for($this->envelope);
            }
        );
    }
}
