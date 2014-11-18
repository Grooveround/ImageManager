<?php
namespace grooveround\image\base;

use grooveround\image\helpers\Image;
use Exception;

/**
 * Class BaseImageDriver
 * @package grooveround\image\drivers
 * @author Derick Fynn <dcfynn@vodamail.co.za>
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
     * @var int width
     */
    protected $width;

    /**
     * Height of the image
     * @var int height
     */
    protected $height;

    /**
     * Type of image
     * @var string fileExtension
     */
    protected $fileExtension;

    /**
     * Image mime type
     * @var string  mimeType
     */
    protected $mimeType;

    /**
     * Loads information about the image
     *
     * @param string $filePath
     * @throws \Exception
     */
    public function __construct($filePath)
    {
        try {
            // Get the real path to the file
            $filePath = realpath($filePath);

            // Get the image information
            $info = getimagesize($filePath);
            if (empty($filePath) || empty($info)) {
                throw new Exception;
            }
            $this->setImageData($filePath, $info);
        } catch (Exception $e) {
            // Ignore all errors while reading the image
        }
    }

    /**
     * Store the image information
     *
     * @param string $filePath
     * @param array $info
     */
    protected function setImageData($filePath, array $info)
    {
        $this->filePath = $filePath;
        list($this->width, $this->height, $this->fileExtension) = $info;
        $this->width = max(round($this->width), 1);
        $this->height = max(round($this->height), 1);
        $this->mimeType = image_type_to_mime_type($this->fileExtension);
    }

}
