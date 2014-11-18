<?php
namespace grooveround\image\drivers;

use grooveround\image\helpers\OptimizerConstant;
use Exception;

/**
 * Class GD
 * @package grooveround\image\drivers
 * @author Derick Fynn <dcfynn@vodamail.co.za>
 */
class Gd extends Image implements ImageDriver
{
    /**
     * Temporary image resource
     *
     * @var string $tmpImage
     */
    protected $tmpImage;

    /**
     * Function name to open Image
     *
     * @var string $createFunctionName
     */
    protected $createFunctionName;

    /**
     * @param string $filePath
     * @throws  \Exception
     */
    public function __construct($filePath)
    {
        parent::__construct($filePath);

        switch ($this->fileExtension) {
            case IMAGETYPE_JPEG:
                $methodName = 'imagecreatefromjpeg';
                break;
            case IMAGETYPE_GIF:
                $methodName = 'imagecreatefromgif';
                break;
            case IMAGETYPE_PNG:
                $methodName = 'imagecreatefrompng';
                break;
        }

        if (!isset($methodName) || !function_exists($methodName)) {
            throw new Exception('Image type not supported');
        }

        $this->createFunctionName = $methodName;
        $this->tmpImage = $this->filePath;
    }

    /**
     * Destroys the loaded image to free up resources.
     *
     * @return  void
     */
    public function __destruct()
    {
        if (is_resource($this->tmpImage)) {
            // Free all resources
            imagedestroy($this->tmpImage);
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $constrain
     * @return mixed|void
     */
    public function resize($width = 0, $height = 0, $constrain = OptimizerConstant::AUTO)
    {
        list($width, $height) = $this->beforeResize($width, $height);

        $preWidth = $this->width;
        $preHeight = $this->height;
        $this->loadImage();

        // Test if we can do a resize without resampling to speed up the final resize
        if ($width > ($this->width / 2) AND $height > ($this->height / 2)) {
            // The maximum reduction is 10% greater than the final size
            $reduction_width = round($width * 1.1);
            $reduction_height = round($height * 1.1);

            while ($preWidth / 2 > $reduction_width AND $preHeight / 2 > $reduction_height) {
                // Reduce the size using an O(2n) algorithm, until it reaches the maximum reduction
                $preWidth /= 2;
                $preHeight /= 2;
            }

            $image = $this->createImage($preWidth, $preHeight);

            if (imagecopyresized($image, $this->tmpImage, 0, 0, 0, 0, $preWidth, $preHeight, $this->width, $this->height)) {
                imagedestroy($this->tmpImage);
                $this->tmpImage = $image;
            }
        }

        $image = $this->createImage($width, $height);

        if (imagecopyresampled($image, $this->tmpImage, 0, 0, 0, 0, $width, $height, $preWidth, $preHeight)) {
            imagedestroy($this->tmpImage);
            $this->tmpImage = $image;
            $this->width = imagesx($image);
            $this->height = imagesy($image);
        }
    }

    /**
     * Crops img
     *
     * @param int $width
     * @param int $height
     * @param int $offsetX
     * @param int $offsetY
     * @return  void
     */
    public function crop($width, $height, $offsetX = 0, $offsetY = 0)
    {
        list($width, $height, $offsetX, $offsetY) = $this->beforeCrop($width, $height, $offsetX, $offsetY);

        $image = $this->createImage($width, $height);
        $this->loadImage();

        if (imagecopyresampled($image, $this->tmpImage, 0, 0, $offsetX, $offsetY, $width, $height, $width, $height)) {
            imagedestroy($this->tmpImage);
            $this->tmpImage = $image;
            $this->width = imagesx($image);
            $this->height = imagesy($image);
        }
    }

    /**
     * Rotates img
     *
     * @param int $degrees
     * @return  void
     */
    public function rotate($degrees)
    {
        list($degrees) = $this->beforeRotate($degrees);

        $this->loadImage();

        $transparent = imagecolorallocatealpha($this->tmpImage, 0, 0, 0, 127);
        $image = imagerotate($this->tmpImage, 360 - $degrees, $transparent, 1);

        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        if (imagecopymerge($this->tmpImage, $image, 0, 0, 0, 0, $width, $height, 100)) {
            imagedestroy($this->tmpImage);
            $this->tmpImage = $image;
            $this->width = $width;
            $this->height = $height;
        }
    }

    /**
     * Flips img
     *
     * @param int $direction direction to flip
     * @return  void
     */
    public function flip($direction)
    {
        $this->beforeFlip($direction);

        $flipped = $this->createImage($this->width, $this->height);
        $this->loadImage();

        if ($direction === OptimizerConstant::HORIZONTAL) {
            for ($x = 0; $x < $this->width; $x++) {
                imagecopy($flipped, $this->tmpImage, $x, 0, $this->width - $x - 1, 0, 1, $this->height);
            }
        } else {
            for ($y = 0; $y < $this->height; $y++) {
                imagecopy($flipped, $this->tmpImage, 0, $y, 0, $this->height - $y - 1, $this->width, 1);
            }
        }

        imagedestroy($this->tmpImage);
        $this->tmpImage = $flipped;
        $this->width = imagesx($flipped);
        $this->height = imagesy($flipped);
    }

    /**
     * Sharpens img
     *
     * @param int $value amount to sharpen
     * @return  void
     */
    public function sharpen($value)
    {
        // TODO: Implement sharpen() method.
    }

    /**
     * Reflection.
     *
     * @param int $height
     * @param int $opacity
     * @param bool $fadeIn
     * @return  void
     */
    public function reflection($height, $opacity, $fadeIn)
    {
        // TODO: Implement reflection() method.
    }

    /**
     * Watermarking.
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
     * Saves
     *
     * @param string $filePath
     * @param int $quality
     * @return  bool
     */
    public function save($filePath = null, $quality)
    {
        list($filePath, $quality) = $this->beforeSave($filePath, $quality);
        $this->loadImage();
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        list($save, $type) = $this->saveImage($extension, $quality);

        $status = isset($quality) ? $save($this->tmpImage, $filePath, $quality) : $save($this->tmpImage, $filePath);

        if ($status === TRUE AND $type !== $this->fileExtension) {
            $this->fileExtension = $type;
            $this->mimeType = image_type_to_mime_type($type);
        }

        return TRUE;
    }

    /**
     * Renders
     *
     * @param string $type
     * @param int $quality
     * @return  string
     */
    public function render($type, $quality)
    {
        // TODO: Implement render() method.
    }

    /**
     * Create an empty image with the given width and height.
     *
     * @param int $width
     * @param int $height
     * @return  resource
     */
    protected function createImage($width, $height)
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * Loads an image into GD.
     *
     * @return  void
     */
    protected function loadImage()
    {
        if (!is_resource($this->tmpImage)) {
            $create = $this->createFunctionName;
            $this->tmpImage = $create($this->filePath);
            imagesavealpha($this->tmpImage, true);
        }
    }

    /**
     * Get the GD saving function and image type for this extension.
     * Also normalizes the quality setting
     *
     * @param string $extension png, jpg, etc
     * @param int $quality image quality
     * @return  array    save function, IMAGETYPE_* constant
     * @throws  \Exception
     */
    protected function saveImage($extension, & $quality)
    {
        if (!$extension) {
            $extension = image_type_to_extension($this->fileExtension, false);
        }

        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                $save = 'imagejpeg';
                $type = IMAGETYPE_JPEG;
                break;
            case 'gif':
                $save = 'imagegif';
                $type = IMAGETYPE_GIF;
                $quality = NULL;
                break;
            case 'png':
                $save = 'imagepng';
                $type = IMAGETYPE_PNG;
                $quality = 9;
                break;
            default:
                throw new Exception('Type not supported');
                break;
        }

        return [$save, $type];
    }
}