<?php
namespace grooveround\image\helpers;

use Exception;

/**
 * Image optimizer, Cropper and Modifier
 *
 * Class Image
 * @package console\modules\helpers
 * @author Derick Fynn <dcfynn@vodamail.co.za>
 */
trait Image
{
    /**
     * Normalises width and height before resizing
     *
     * @param mixed|null|int $width
     * @param mixed|null|int $height
     * @param int $constrain
     * @return array
     */
    public function beforeResize($width = null, $height = null, $constrain = OptimizerConstant::AUTO)
    {

        list($constrain, $width, $height) = $this->predictResizeOrientation($constrain, $width, $height);

        switch ($constrain) {
            case OptimizerConstant::INVERSE:
                $constrain = ($this->width / $width) > ($this->height / $height) ? OptimizerConstant::HEIGHT : OptimizerConstant::WIDTH;
                break;
            case OptimizerConstant::AUTO:
                $constrain = ($this->width / $width) > ($this->height / $height) ? OptimizerConstant::WIDTH : OptimizerConstant::HEIGHT;
                break;
        }

        switch ($constrain) {
            case OptimizerConstant::PRECISE:
                $ratio = $this->width / $this->height;

                if ($width / $height > $ratio) {
                    $height = $this->height * $width / $this->width;
                } else {
                    $width = $this->width * $height / $this->height;
                }
                break;
            case OptimizerConstant::WIDTH:
                $height = $this->height * $width / $this->width;
                break;
            case OptimizerConstant::HEIGHT:
                $width = $this->width * $height / $this->height;
                break;
        }

        $width = max(round($width), 1);
        $height = max(round($height), 1);

        return [$width, $height];
    }

    /**
     * Normalises width and height before cropping
     *
     * @param int $width new width
     * @param int $height new height
     * @param mixed $offsetX offset from the left
     * @param mixed $offsetY offset from the top
     * @return  $this
     */
    public function beforeCrop($width, $height, $offsetX = 0, $offsetY = 0)
    {
        $width = ($width > $this->width) ? $this->width : $width;
        $height = ($height > $this->height) ? $this->height : $height;

        if (!$offsetX) {
            $offsetX = round(($this->width - $width) / 2);
        } elseif ($offsetX) {
            $offsetX = $this->width - $width;
        } elseif ($offsetX < 0) {
            $offsetX = $this->width - $width + $offsetX;
        }

        if (!$offsetY) {
            $offsetY = round(($this->height - $height) / 2);
        } elseif ($offsetY) {
            $offsetY = $this->height - $height;
        } elseif ($offsetY < 0) {
            $offsetY = $this->height - $height + $offsetY;
        }

        $maxWidth = $this->width - $offsetX;
        $maxHeight = $this->height - $offsetY;
        $width = ($width > $maxWidth) ? $maxWidth : $width;
        $height = ($height > $maxHeight) ? $maxHeight : $height;

        return [$width, $height, $offsetX, $offsetY];
    }

    /**
     * Normalises width and height
     *
     * @param int $degrees degrees to rotate: -360-360
     * @return  $this
     */
    public function beforeRotate($degrees)
    {
        $degrees = (int)$degrees;

        if ($degrees > 180) {
            while ($degrees > 180) {
                $degrees -= 360;
            }
        }

        if ($degrees < -180) {
            while ($degrees < -180) {
                $degrees += 360;
            }
        }

        return [$degrees];
    }

    /**
     * Prepares for image flip
     *
     * @param int $direction direction: OptimizerConstant::HORIZONTAL, OptimizerConstant::VERTICAL
     * @return  $this
     */
    public function beforeFlip($direction)
    {
        if ($direction !== OptimizerConstant::HORIZONTAL) {
            $direction = OptimizerConstant::VERTICAL;
        }

        return [$direction];
    }

    /**
     * Prepares for image sharpen
     *
     * @param int $value
     * @return  $this
     */
    public function beforeSharpen($value)
    {
        $value = min(max($value, 1), 100);
        return [$value];
    }

    /**
     * Normalises before performing image reflection
     *
     * @param int $height
     * @param int $opacity
     * @param boolean $fadeIn
     * @return  $this
     */
    public function beforeReflection($height = null, $opacity = 100, $fadeIn = false)
    {
        if ($height === null || $height > $this->height) {
            $height = $this->height;
        }

        $opacity = min(max($opacity, 0), 100);
        return [$height, $opacity, $fadeIn];
    }

    /**
     * Normalise before watermark
     *
     * @param Image $watermark watermark Image instance
     * @param int $offsetX
     * @param int $offsetY
     * @param int $opacity
     * @return  $this
     */
    public function beforeWatermark(Image $watermark, $offsetX = null, $offsetY = null, $opacity = 100)
    {
        if ($offsetX === null) {
            $offsetX = round(($this->width - $watermark->width) / 2);
        } elseif ($offsetX === true) {
            $offsetX = $this->width - $watermark->width;
        } elseif ($offsetX < 0) {
            $offsetX = $this->width - $watermark->width + $offsetX;
        }

        if ($offsetY === null) {
            $offsetY = round(($this->height - $watermark->height) / 2);
        } elseif ($offsetY === true) {
            $offsetY = $this->height - $watermark->height;
        } elseif ($offsetY < 0) {
            $offsetY = $this->height - $watermark->height + $offsetY;
        }

        $opacity = min(max($opacity, 1), 100);
        return [$watermark, $offsetX, $offsetY, $opacity];
    }

    /**
     * Normalizes before background
     *
     * @param string $color
     * @param int $opacity
     * @return  $this
     */
    public function beforeBackground($color, $opacity = 100)
    {
        if ($color[0] === '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) === 3) {
            $color = preg_replace('/./', '$0$0', $color);
        }

        list ($red, $green, $blue) = array_map('hexdec', str_split($color, 2));
        $opacity = min(max($opacity, 0), 100);
        return [$red, $green, $blue, $opacity];
    }

    /**
     * Normalises before saving
     *
     * @param string $filePath new image path
     * @param int $quality quality of image: 1-100
     * @return  array
     * @throws  \Exception
     */
    public function beforeSave($filePath = null, $quality = 100)
    {
        if ($filePath === null) {
            $filePath = $this->filePath;
        }

        if (is_file($filePath)) {
            if (!is_writable($filePath)) {
                throw new Exception('File must be writable: :file');
            }
        } else {
            $directory = realpath(pathinfo($filePath, PATHINFO_DIRNAME));

            if (!is_dir($directory) || !is_writable($directory)) {
                throw new Exception('Directory must be writable: :directory');
            }
        }

        $quality = min(max($quality, 1), 100);
        return [$filePath, $quality];
    }

    /**
     * Normalize before rendering
     *
     * @param string $type image type to return: png, jpg, gif, etc
     * @param int $quality quality of image: 1-100
     * @return  string
     */
    public function beforeRender($type = null, $quality = 100)
    {
        if ($type === null) {
            $type = image_type_to_extension($this->fileExtension, false);
        }

        return [$type, $quality];
    }

    /**
     * @param int $constrain
     * @param int $width
     * @param int $height
     * @return array
     */
    private function predictResizeOrientation($constrain, $width, $height)
    {
        if ($constrain == OptimizerConstant::WIDTH && !empty($width)) {
            $constrain = OptimizerConstant::AUTO;
            $height = null;
        } elseif (($constrain == OptimizerConstant::HEIGHT) && !empty($height)) {
            $constrain = OptimizerConstant::AUTO;
            $width = null;
        }

        if (empty($width)) {
            if ($constrain === OptimizerConstant::NONE) {
                $width = $this->width;
            } else {
                $constrain = OptimizerConstant::HEIGHT;
            }
        }

        if (empty($height)) {
            if ($constrain === OptimizerConstant::NONE) {
                $height = $this->height;
            } else {
                $constrain = OptimizerConstant::WIDTH;
            }
        }

        return [$constrain, $width, $height];
    }

}

