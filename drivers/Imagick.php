<?php
namespace grooveround\image\drivers;

use grooveround\image\helpers\OptimizerConstant;
use Exception;
use Imagick as ImagickLib;
use ImagickPixel;

/**
 * Class Imagick
 * @package grooveround\image\drivers
 * @author Derick Fynn <dcfynn@vodamail.co.za>
 */
class Imagick extends Image implements ImageDriver
{
    private $image;

    /**
     * @param $filePath
     */
    public function __construct($filePath)
    {
        parent::__construct($filePath);

        $this->image = new ImagickLib();
        $this->image->readImage($filePath);

        if (!$this->image->getImageAlphaChannel()) {
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
    public function resize($width = 0, $height = 0, $constrain = OptimizerConstant::AUTO)
    {
        list($width, $height) = $this->beforeResize($width, $height);

        if ($this->image->scaleImage($width, $height)) {
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();
            return true;
        }

        return false;
    }

    /**
     * Crop image
     *
     * @param int $width
     * @param int $height
     * @param int $offsetX
     * @param int $offsetY
     * @return  bool
     */
    public function crop($width, $height, $offsetX = 0, $offsetY = 0)
    {
        list($width, $height, $offsetX, $offsetY) = $this->beforeCrop($width, $height, $offsetX, $offsetY);

        if ($this->image->cropImage($width, $height, $offsetX, $offsetY)) {
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();
            $this->image->setImagePage($this->width, $this->height, 0, 0);

            return true;
        }

        return false;
    }

    /**
     * Rotates image
     *
     * @param int $degrees
     * @return  bool
     */
    public function rotate($degrees)
    {
        list($degrees) = $this->beforeRotate($degrees);

        if ($this->image->rotateImage(new ImagickPixel('transparent'), $degrees)) {
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();
            // Removes hidden areas
            $this->image->setImagePage($this->width, $this->height, 0, 0);

            return true;
        }
        return false;
    }

    /**
     * Flips image
     *
     * @param int $direction
     * @return  bool
     */
    public function flip($direction)
    {
        $this->beforeFlip($direction);

        if ($direction === OptimizerConstant::HORIZONTAL) {
            return $this->image->flopImage();
        } else {
            return $this->image->flipImage();
        }
    }

    /**
     * Sharpens image
     *
     * @param int $value
     * @return  void
     */
    public function sharpen($value)
    {
        // TODO: Implement sharpen() method.
    }

    /**
     * Reflects image
     *
     * @param int $height
     * @param int $opacity
     * @param boolean $fadeIn
     * @return  void
     */
    public function reflection($height, $opacity, $fadeIn)
    {
        // TODO: Implement reflection() method.
    }

    /**
     * Watermarking
     *
     * @param Image $image
     * @param int $offsetX
     * @param int $offsetY
     * @param int $opacity
     * @return  void
     */
    public function watermark(Image $image, $offsetX, $offsetY, $opacity)
    {
        // TODO: Implement watermark() method.
    }

    /**
     * Background.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $opacity
     * @return void
     */
    public function background($red, $green, $blue, $opacity)
    {
        // TODO: Implement background() method.
    }

    /**
     * Save changes
     *
     * @param string $filePath
     * @param int $quality
     * @return  boolean
     */
    public function save($filePath = null, $quality)
    {
        list($file, $quality) = $this->beforeSave($filePath, $quality);
        list($format, $type) = $this->getImageType(pathinfo($file, PATHINFO_EXTENSION));

        $this->image->setFormat($format);
        $this->image->setImageCompressionQuality($quality);

        if ($this->image->writeImage($file)) {
            $this->fileExtension = $type;
            $this->mimeType = image_type_to_mime_type($type);

            return true;
        }

        return false;
    }

    /**
     * Renders image
     *
     * @param string $type
     * @param int $quality
     * @return string
     */
    public function render($type, $quality)
    {
        // TODO: Implement render() method.
    }

    /**
     * Get the image type and format for an extension.
     *
     * @param string $extension
     * @return  string
     * @throws  Exception
     */
    protected function getImageType($extension)
    {
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
                throw new Exception("Type not supported by Imagick");
                break;
        }

        return [$format, $type];
    }
}