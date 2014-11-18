<?php
namespace grooveround\image\base;

use grooveround\image\helpers\Image;
use Exception;

/**
 * Class BaseImageDriver
 * @package grooveround\image\drivers
 * @author Deick Fynn <dcfynn@vodamail.co.za>
 */
abstract class BaseImage
{
    use Image;

    /**
     * Path to image
     * @var string $file
     */
    protected $filePath;

    /**
     * Width of the image
     * @var integer width
     */
    protected $width;

    /**
     * Height of the image
     * @var integer height
     */
    protected $height;

    /**
     * Type of image
     * @var integer type
     */
    protected $fileExtension;

    /**
     * Image mime type
     * @var string  mime
     */
    protected $mimeType;

    /**
     * Loads information about the image. Will throw an exception if the image
     * does not exist or is not an image.
     * @param string $file image file path
     * @throws \Exception
     */
    public function __construct($file)
    {
        try {
            // Get the real path to the file
            $file = realpath($file);

            // Get the image information
            $info = getimagesize($file);
            if (empty($file) || empty($info)) {
                throw new Exception;
            }
            $this->setImageData($file, $info);
        } catch (Exception $e) {
            // Ignore all errors while reading the image
        }
    }

    /**
     * Store the image information
     *
     * @param string $file
     * @param array $info
     */
    protected function setImageData($file, array $info)
    {
        $this->filePath = $file;
        list($this->width, $this->height, $this->fileExtension) = $info;
        $this->width = max(round($this->width), 1);
        $this->height = max(round($this->height), 1);
        $this->mimeType = image_type_to_mime_type($this->fileExtension);
    }

}
