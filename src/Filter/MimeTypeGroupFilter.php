<?php

namespace Markup\Contentful\Filter;

use Markup\Contentful\Property\MimeTypeGroup;

class MimeTypeGroupFilter extends EqualFilter
{
    const VALUE_ATTACHMENT = 'attachment';
    const VALUE_PLAINTEXT = 'plaintext';
    const VALUE_IMAGE = 'image';
    const VALUE_AUDIO = 'audio';
    const VALUE_VIDEO = 'video';
    const VALUE_RICHTEXT = 'richtext';
    const VALUE_PRESENTATION = 'presentation';
    const VALUE_SPREADSHEET = 'spreadsheet';
    const VALUE_PDF_DOCUMENT = 'pdf_document';
    const VALUE_ARCHIVE = 'archive';
    const VALUE_CODE = 'code';
    const VALUE_MARKUP = 'markup';

    /**
     * @param string $mimeTypeGroup The MIME type group - must be one of VALUE_* class constants.
     */
    public function __construct($mimeTypeGroup)
    {
        $constants = array_values((new \ReflectionObject($this))->getConstants());
        if (!in_array($mimeTypeGroup, $constants)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a known MIME type group. Known types: %s.',
                $mimeTypeGroup,
                implode(', ', $constants)
            ));
        }
        parent::__construct(new MimeTypeGroup(), $mimeTypeGroup);
    }

    public function getName()
    {
        return 'mimetype_group';
    }
}
