<?php
namespace grooveround\image;

use grooveround\image\drivers\ImageDriver;
use grooveround\image\helpers\OptimizerConstant;

/**
 * Class ImageManager
 * @package grooveround\image
 * @author Derick Fynn <dcfynn@vodamail.co.za>
 */
class ImageManager
{
    /**
     * @var array $drivers Image drivers e.g GD, Imagick, etc
     */
    private $drivers = [];

    /**
     * Adds Image driver instance
     *
     * @param string $identifier
     * @param ImageDriver $driver
     * @return $this
     */
    public function addDriver($identifier, ImageDriver $driver)
    {
        if (ctype_alpha($identifier)) {
            $this->drivers[$identifier] = $driver;
        }
        return $this;
    }

    /**
     * Retrieve image driver instance
     *
     * @param string $identifier
     * @return mixed|bool|ImageDriver
     */
    public function getDriver($identifier)
    {
        if (ctype_alpha($identifier) && array_key_exists($identifier, $this->drivers)) {
            if ($this->drivers[$identifier] instanceof ImageDriver) {
                return $this->drivers[$identifier];
            }
        }
        return false;
    }

    /**
     * Resize image
     *
     * @param string $driver
     * @param int $width
     * @param int $height
     * @param int $constrain
     */
    public function resizeImage($driver, $width = 0, $height = 0, $constrain = OptimizerConstant::AUTO)
    {
        $this->getDriver($driver)->resize($width, $height, $constrain);
    }

    /**
     * Crops image
     *
     * @param string $driver
     * @param int $width
     * @param int $height
     * @param int $offsetX
     * @param int $offsetY
     * @return $this
     */
    public function cropImage($driver, $width, $height, $offsetX = 0, $offsetY = 0)
    {
        $this->getDriver($driver)->crop($width, $height, $offsetX, $offsetY);
        return $this;
    }

    /**
     * Rotates image
     *
     * @param string $driver
     * @param int $degrees
     * @return $this
     */
    public function rotateImage($driver, $degrees)
    {
        $this->getDriver($driver)->rotate($degrees);
        return $this;
    }

    /**
     * Flips image
     *
     * @param string $driver
     * @param int $direction
     * @return $this
     */
    public function flipImage($driver, $direction)
    {
        $this->getDriver($driver)->flip($direction);
        return $this;
    }

    /**
     * Saves image
     *
     * @param string $driver
     * @param string $path
     * @param int $quality
     */
    public function saveImage($driver, $path, $quality = 100)
    {
        $this->getDriver($driver)->save($path, $quality);
    }
}