<?php
namespace grooveround\image;

use grooveround\image\drivers\ImageDriver;

/**
 * Class ImageManager
 * @package grooveround\image
 * @author Deick Fynn <dcfynn@vodamail.co.za>
 */
class ImageManager
{
    /**
     * @var array $drivers Image drivers e.g GD, Imagick, etc
     */
    private $drivers = [];

    /**
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
     * @param $driver
     * @param $width
     * @param $height
     */
    public function resizeImage($driver, $width, $height)
    {
        $this->getDriver($driver)->resize($width, $height);
    }

    /**
     * @param $driver
     * @param $width
     * @param $height
     * @param $offsetX
     * @param $offsetY
     * @return $this
     */
    public function cropImage($driver, $width, $height, $offsetX, $offsetY)
    {
        $this->getDriver($driver)->crop($width, $height, $offsetX, $offsetY);
        return $this;
    }

    /**
     * @param $driver
     * @param $degrees
     * @return $this
     */
    public function rotateImage($driver, $degrees)
    {
        $this->getDriver($driver)->rotate($degrees);
        return $this;
    }

    /**
     * @param $driver
     * @param $direction
     * @return $this
     */
    public function flipImage($driver, $direction)
    {
        $this->getDriver($driver)->flip($direction);
        return $this;
    }

    /**
     * @param $driver
     * @param $path
     * @param int $quality
     */
    public function saveImage($driver, $path, $quality = 100)
    {
        $this->getDriver($driver)->save($path, $quality);
    }
}