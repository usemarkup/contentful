<?php

namespace Markup\Contentful;

/**
 * An object representing a set of options for the Image API @see http://docs.contentfulimagesapi.apiary.io/
 */
class ImageApiOptions
{
    //options from userland code should use the following values for option keys when using createFromHumanOptions():
    //"image_format"
    const FORMAT_JPEG = 'jpg';
    const FORMAT_PNG = 'png';

    //"quality"
    //takes an integer between 0 and 100

    //"progressive"
    //takes a boolean
    const VALUE_PROGRESSIVE = 'progressive';

    //"width"
    //takes a float, representing pixels
    //"height"
    //takes a float, representing pixels

    //"fit"
    const FIT_PAD = 'pad';
    const FIT_CROP = 'crop';
    const FIT_FILL = 'fill';
    const FIT_THUMB = 'thumb';
    const FIT_SCALE = 'scale';

    //"focus"
    const FOCUS_TOP = 'top';
    const FOCUS_BOTTOM = 'bottom';
    const FOCUS_LEFT = 'left';
    const FOCUS_RIGHT = 'right';
    const FOCUS_TOP_LEFT = 'top_left';
    const FOCUS_TOP_RIGHT = 'top_right';
    const FOCUS_BOTTOM_LEFT = 'bottom_left';
    const FOCUS_BOTTOM_RIGHT = 'bottom_right';
    const FOCUS_FACE = 'face';
    const FOCUS_FACES = 'faces';

    //"radius"
    //takes a float, representing pixels forming radius at corners

    //"background_color"
    //takes an RGB value, in full e.g. "34AB23"

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options The raw Image API params.
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param array $options
     * @return self
     */
    public static function createFromHumanOptions(array $options)
    {
        $apiOptions = new self();
        if (isset($options['image_format'])) {
            $apiOptions->setImageFormat($options['image_format']);
        }
        if (isset($options['quality'])) {
            $apiOptions->setQuality($options['quality']);
        }
        if (isset($options['progressive'])) {
            $apiOptions->setProgressive($options['progressive']);
        }
        if (isset($options['width'])) {
            $apiOptions->setWidth($options['width']);
        }
        if (isset($options['height'])) {
            $apiOptions->setHeight($options['height']);
        }
        if (isset($options['fit'])) {
            $apiOptions->setFit($options['fit'], (isset($options['focus'])) ? $options['focus'] : null);
        }
        if (isset($options['radius'])) {
            $apiOptions->setRadius($options['radius']);
        }
        if (isset($options['background_color'])) {
            $apiOptions->setBackgroundColor($options['background_color']);
        }

        return $apiOptions;
    }

    public function toArray()
    {
        return $this->options;
    }

    /**
     * @param string $format Expected values are the FORMAT_* class constants
     */
    public function setImageFormat($format)
    {
        $this->options['fm'] = $format;
    }

    /**
     * @param int $quality A value between 0 and 100 for quality (100 is high).
     */
    public function setQuality($quality)
    {
        $this->options['q'] = intval($quality);
    }

    /**
     * @param bool $whether Whether to use progressive rendering.
     */
    public function setProgressive($whether)
    {
        if ($whether) {
            $this->options['fl'] = self::VALUE_PROGRESSIVE;
        } else {
            if (array_key_exists('fl', $this->options)) {
                unset($this->options['fl']);
            }
        }
    }

    /**
     * @param int $widthInPixels
     */
    public function setWidth($widthInPixels)
    {
        $this->options['w'] = intval($widthInPixels);
    }

    /**
     * @param int $heightInPixels
     */
    public function setHeight($heightInPixels)
    {
        $this->options['h'] = intval($heightInPixels);
    }

    /**
     * @param string  $fit   A FIT_* class constant value
     * @param string  $focus A FOCUS_* class constant value. Will be ignored if $fit is not 'thumb'
     */
    public function setFit($fit, $focus = null)
    {
        $this->options['fit'] = $fit;
        if ($fit === self::FIT_THUMB) {
            $this->options['f'] = $focus;
        }
    }

    /**
     * @param int $radiusInPixels
     */
    public function setRadius($radiusInPixels)
    {
        $this->options['r'] = intval($radiusInPixels);
    }

    /**
     * @param string $backgroundColor An RGB color in 6 character hex format (e.g. "12a3f5")
     */
    public function setBackgroundColor($backgroundColor)
    {
        if (!preg_match('/^[0-9a-f]{6}$/i', $backgroundColor)) {
            return;
        }
        $this->options['bg'] = 'rgb:' . strtolower($backgroundColor);
    }
}
