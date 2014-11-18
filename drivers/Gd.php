<?php
namespace grooveround\image\drivers;

use grooveround\image\helpers\ResizingConstraint;
use Exception;

/**
 * Class GD
 * @package grooveround\image\drivers
 * @author Deick Fynn <dcfynn@vodamail.co.za>
 */
class Gd extends Image implements ImageDriver
{
    // Temporary image resource
    protected $tmpImage;

    // Function name to open Image
    protected $createFunctionName;

    /**
     * @param   string $file image file path
     * @throws  \Exception
     */
    public function __construct($file)
    {
        parent::__construct($file);

        // Set the image creation function name
        switch ($this->fileExtension) {
            case IMAGETYPE_JPEG:
                $create = 'imagecreatefromjpeg';
                break;
            case IMAGETYPE_GIF:
                $create = 'imagecreatefromgif';
                break;
            case IMAGETYPE_PNG:
                $create = 'imagecreatefrompng';
                break;
        }

        if (!isset($create) || !function_exists($create)) {
            throw new Exception('Installed GD does not support :type images');
        }

        // Save function for future use
        $this->createFunctionName = $create;

        // Save filename for lazy loading
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
    public function resize($width = 0, $height = 0, $constrain = ResizingConstraint::AUTO)
    {
        list($width, $height) = $this->beforeResize($width, $height);

        // Presize width and height
        $preWidth = $this->width;
        $preHeight = $this->height;

        // Loads image if not yet loaded
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

            // Create the temporary image to copy to
            $image = $this->createImage($preWidth, $preHeight);

            if (imagecopyresized($image, $this->tmpImage, 0, 0, 0, 0, $preWidth, $preHeight, $this->width, $this->height)) {
                // Swap the new image for the old one
                imagedestroy($this->tmpImage);
                $this->tmpImage = $image;
            }
        }

        // Create the temporary image to copy to
        $image = $this->createImage($width, $height);

        // Execute the resize
        if (imagecopyresampled($image, $this->tmpImage, 0, 0, 0, 0, $width, $height, $preWidth, $preHeight)) {
            // Swap the new image for the old one
            imagedestroy($this->tmpImage);
            $this->tmpImage = $image;

            // Reset the width and height
            $this->width = imagesx($image);
            $this->height = imagesy($image);
        }
    }

    /**
     * Crops img
     *
     * @param   integer $width new width
     * @param   integer $height new height
     * @param   integer $offsetX offset from the left
     * @param   integer $offsetY offset from the top
     * @return  void
     */
    public function crop($width, $height, $offsetX = 0, $offsetY = 0)
    {
        list($width, $height, $offsetX, $offsetY) = $this->beforeCrop($width, $height, $offsetX, $offsetY);

        // Create the temporary image to copy to
        $image = $this->createImage($width, $height);

        // Loads image if not yet loaded
        $this->loadImage();

        // Execute the crop
        if (imagecopyresampled($image, $this->tmpImage, 0, 0, $offsetX, $offsetY, $width, $height, $width, $height)) {
            // Swap the new image for the old one
            imagedestroy($this->tmpImage);
            $this->tmpImage = $image;

            // Reset the width and height
            $this->width = imagesx($image);
            $this->height = imagesy($image);
        }
    }

    /**
     * Rotates img
     *
     * @param   integer $degrees degrees to rotate
     * @return  void
     */
    public function rotate($degrees)
    {
        list($degrees) = $this->beforeRotate($degrees);


        // Loads image if not yet loaded
        $this->loadImage();

        // Transparent black will be used as the background for the uncovered region
        $transparent = imagecolorallocatealpha($this->tmpImage, 0, 0, 0, 127);

        // Rotate, setting the transparent color
        $image = imagerotate($this->tmpImage, 360 - $degrees, $transparent, 1);

        // Save the alpha of the rotated image
        imagesavealpha($image, true);

        // Get the width and height of the rotated image
        $width = imagesx($image);
        $height = imagesy($image);

        if (imagecopymerge($this->tmpImage, $image, 0, 0, 0, 0, $width, $height, 100)) {
            // Swap the new image for the old one
            imagedestroy($this->tmpImage);
            $this->tmpImage = $image;

            // Reset the width and height
            $this->width = $width;
            $this->height = $height;
        }
    }

    /**
     * Flips img
     *
     * @param   integer $direction direction to flip
     * @return  void
     */
    public function flip($direction)
    {
        $this->beforeFlip($direction);

        // Create the flipped image
        $flipped = $this->createImage($this->width, $this->height);

        // Loads image if not yet loaded
        $this->loadImage();

        if ($direction === ResizingConstraint::HORIZONTAL) {
            for ($x = 0; $x < $this->width; $x++) {
                // Flip each row from top to bottom
                imagecopy($flipped, $this->tmpImage, $x, 0, $this->width - $x - 1, 0, 1, $this->height);
            }
        } else {
            for ($y = 0; $y < $this->height; $y++) {
                // Flip each column from left to right
                imagecopy($flipped, $this->tmpImage, 0, $y, 0, $this->height - $y - 1, $this->width, 1);
            }
        }

        // Swap the new image for the old one
        imagedestroy($this->tmpImage);
        $this->tmpImage = $flipped;

        // Reset the width and height
        $this->width = imagesx($flipped);
        $this->height = imagesy($flipped);
    }

    /**
     * Sharpens img
     *
     * @param   integer $amount amount to sharpen
     * @return  void
     */
    public function sharpen($amount)
    {
        // TODO: Implement sharpen() method.
    }

    /**
     * Reflection.
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
     * Watermarking.
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
     * Saves
     *
     * @param   string $filePath new image filename
     * @param   integer $quality quality
     * @return  boolean
     */
    public function save($filePath =null, $quality)
    {
        list($file, $quality) = $this->beforeSave($filePath, $quality);
        // Loads image if not yet loaded
        $this->loadImage();

        // Get the extension of the file
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        // Get the save function and IMAGETYPE
        list($save, $type) = $this->saveImage($extension, $quality);

        // Save the image to a file
        $status = isset($quality) ? $save($this->tmpImage, $file, $quality) : $save($this->tmpImage, $file);

        if ($status === TRUE AND $type !== $this->fileExtension) {
            // Reset the image type and mime type
            $this->fileExtension = $type;
            $this->mimeType = image_type_to_mime_type($type);
        }

        return TRUE;
    }

    /**
     * Renders
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
     * Create an empty image with the given width and height.
     *
     * @param   integer $width image width
     * @param   integer $height image height
     * @return  resource
     */
    protected function createImage($width, $height)
    {
        // Create an empty image
        $image = imagecreatetruecolor($width, $height);

        // Do not apply alpha blending
        imagealphablending($image, false);

        // Save alpha levels
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
            // Gets create function
            $create = $this->createFunctionName;

            // Open the temporary image
            $this->tmpImage = $create($this->filePath);

            // Preserve transparency when saving
            imagesavealpha($this->tmpImage, true);
        }
    }

    /**
     * Get the GD saving function and image type for this extension.
     * Also normalizes the quality setting
     *
     * @param   string $extension png, jpg, etc
     * @param   integer $quality image quality
     * @return  array    save function, IMAGETYPE_* constant
     * @throws  \Exception
     */
    protected function saveImage($extension, & $quality)
    {
        if (!$extension) {
            // Use the current image type
            $extension = image_type_to_extension($this->fileExtension, false);
        }

        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                // Save a JPG file
                $save = 'imagejpeg';
                $type = IMAGETYPE_JPEG;
                break;
            case 'gif':
                // Save a GIF file
                $save = 'imagegif';
                $type = IMAGETYPE_GIF;

                // GIFs do not a quality setting
                $quality = NULL;
                break;
            case 'png':
                // Save a PNG file
                $save = 'imagepng';
                $type = IMAGETYPE_PNG;

                // Use a compression level of 9 (does not affect quality!)
                $quality = 9;
                break;
            default:
                throw new Exception('Installed GD does not support :type images');
                break;
        }

        return [$save, $type];
    }
}