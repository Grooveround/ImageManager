<?php
namespace grooveround\image\drivers;

use grooveround\image\helpers\ResizingConstraint;
use Exception;
use Imagick as ImagickLib;
use ImagickPixel;

/**
 * Class Imagick
 * @package grooveround\image\drivers
 * @author Deick Fynn <dcfynn@vodamail.co.za>
 */
class Imagick extends Image implements ImageDriver
{
    private $image;

    /**
     * @param $file
     */
    public function __construct($file)
    {
        parent::__construct($file);

        $this->image = new ImagickLib();
        $this->image->readImage($file);

        if (!$this->image->getImageAlphaChannel()) {
            // Force the image to have an alpha channel
            $this->image->setImageAlphaChannel(ImagickLib::ALPHACHANNEL_SET);
        }
    }

    /**
     * Destroys the loaded image to free up resources.
     *
     * @return  void
     */
    public function __destruct()
    {
        $this->image->clear();
        $this->image->destroy();
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $constrain
     * @return bool|mixed
     */
    public function resize($width = 0, $height = 0, $constrain = ResizingConstraint::AUTO)
    {
        list($width, $height) = $this->beforeResize($width, $height);

        if ($this->image->scaleImage($width, $height)) {
            // Reset the width and height
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();

            return true;
        }

        return false;
    }

    /**
     * Crop image
     *
     * @param   integer $width new width
     * @param   integer $height new height
     * @param   integer $offsetX offset from the left
     * @param   integer $offsetY offset from the top
     * @return  bool
     */
    public function crop($width, $height, $offsetX = 0, $offsetY = 0)
    {
        list($width, $height, $offsetX, $offsetY) = $this->beforeCrop($width, $height, $offsetX, $offsetY);

        if ($this->image->cropImage($width, $height, $offsetX, $offsetY)) {
            // Reset the width and height
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();

            // Trim off hidden areas
            $this->image->setImagePage($this->width, $this->height, 0, 0);

            return true;
        }

        return false;
    }

    /**
     * Rotates image
     *
     * @param   integer $degrees degrees to rotate
     * @return  bool
     */
    public function rotate($degrees)
    {
        list($degrees) = $this->beforeRotate($degrees);

        if ($this->image->rotateImage(new ImagickPixel('transparent'), $degrees)) {
            // Reset the width and height
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();

            // Trim off hidden areas
            $this->image->setImagePage($this->width, $this->height, 0, 0);

            return true;
        }
        return false;
    }

    /**
     * Flips image
     *
     * @param   integer $direction direction to flip
     * @return  bool
     */
    public function flip($direction)
    {
        $this->beforeFlip($direction);

        if ($direction === ResizingConstraint::HORIZONTAL) {
            return $this->image->flopImage();
        } else {
            return $this->image->flipImage();
        }
    }

    /**
     * Sharpens image
     *
     * @param   integer $amount amount to sharpen
     * @return  void
     */
    public function sharpen($amount)
    {
        // TODO: Implement sharpen() method.
    }

    /**
     * Reflects image
     *
     * @param   integer $height reflection height
     * @param   integer $opacity reflection opacity
     * @param   boolean $fadeIn true to fade out, false to fade in
     * @return  void
     */
    public function reflection($height, $opacity, $fadeIn)
    {
        // TODO: Implement reflection() method.
    }

    /**
     * Watermarking
     *
     * @param   Image $image watermarking Image
     * @param   integer $offsetX offset from the left
     * @param   integer $offsetY offset from the top
     * @param   integer $opacity opacity of watermark
     * @return  void
     */
    public function watermark(Image $image, $offsetX, $offsetY, $opacity)
    {
        // TODO: Implement watermark() method.
    }

    /**
     * Background.
     *
     * @param   integer $red red channel
     * @param   integer $green green channel
     * @param   integer $blue blue channel
     * @param   integer $opacity opacity
     * @return void
     */
    public function background($red, $green, $blue, $opacity)
    {
        // TODO: Implement background() method.
    }

    /**
     * Save changes
     *
     * @param   string $filePath new image filename
     * @param   integer $quality quality
     * @return  boolean
     */
    public function save($filePath = null, $quality)
    {
        list($file, $quality) = $this->beforeSave($filePath, $quality);

        // Get the image format and type
        list($format, $type) = $this->getImageType(pathinfo($file, PATHINFO_EXTENSION));

        // Set the output image type
        $this->image->setFormat($format);

        // Set the output quality
        $this->image->setImageCompressionQuality($quality);

        if ($this->image->writeImage($file)) {
            // Reset the image type and mime type
            $this->type = $type;
            $this->mime = image_type_to_mime_type($type);

            return true;
        }

        return false;
    }

    /**
     * Renders image
     *
     * @param   string $type image type: png, jpg, gif, etc
     * @param   integer $quality quality
     * @return  string
     */
    public function render($type, $quality)
    {
        // TODO: Implement render() method.
    }

    /**
     * Get the image type and format for an extension.
     *
     * @param   string $extension image extension: png, jpg, etc
     * @return  string  IMAGETYPE_* constant
     * @throws  Exception
     */
    protected function getImageType($extension)
    {
        // Normalize the extension to a format
        $format = strtolower($extension);

        switch ($format) {
            case 'jpg':
            case 'jpeg':
                $type = IMAGETYPE_JPEG;
                break;
            case 'gif':
                $type = IMAGETYPE_GIF;
                break;
            case 'png':
                $type = IMAGETYPE_PNG;
                break;
            default:
                throw new Exception('Installed ImageMagick does not support :type images');
                break;
        }

        return [$format, $type];
    }
}