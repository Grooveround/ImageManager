<?php
namespace grooveround\image\helpers;

use Exception;

/**
 * Image manipulation support. Allows images to be resize, cropped, etc.
 *
 * Class Image
 * @package console\modules\helpers
 * @author Deick Fynn <dcfynn@vodamail.co.za>
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
    public function beforeResize($width = null, $height = null, $constrain = ResizingConstraint::AUTO)
    {
        if ($constrain == ResizingConstraint::WIDTH && !empty($width)) {
            $constrain = ResizingConstraint::AUTO;

            // Set empty height for backward compatibility
            $height = null;
        } elseif (($constrain == ResizingConstraint::HEIGHT) && !empty($height)) {
            $constrain = ResizingConstraint::AUTO;

            // Set empty width for backward compatibility
            $width = null;
        }

        if (empty($width)) {
            if ($constrain === ResizingConstraint::NONE) {
                // Use the current width
                $width = $this->width;
            } else {
                // If width not set, master will be height
                $constrain = ResizingConstraint::HEIGHT;
            }
        }

        if (empty($height)) {
            if ($constrain === ResizingConstraint::NONE) {
                // Use the current height
                $height = $this->height;
            } else {
                // If height not set, master will be width
                $constrain = ResizingConstraint::WIDTH;
            }
        }

        switch ($constrain) {
            case ResizingConstraint::AUTO:
                // Choose direction with the greatest reduction ratio
                $constrain = ($this->width / $width) > ($this->height / $height) ? ResizingConstraint::WIDTH : ResizingConstraint::HEIGHT;
                break;
            case ResizingConstraint::INVERSE:
                // Choose direction with the minimum reduction ratio
                $constrain = ($this->width / $width) > ($this->height / $height) ? ResizingConstraint::HEIGHT : ResizingConstraint::WIDTH;
                break;
        }

        switch ($constrain) {
            case ResizingConstraint::WIDTH:
                // Recalculate the height based on the width proportions
                $height = $this->height * $width / $this->width;
                break;
            case ResizingConstraint::HEIGHT:
                // Recalculate the width based on the height proportions
                $width = $this->width * $height / $this->height;
                break;
            case ResizingConstraint::PRECISE:
                // Resize to precise size
                $ratio = $this->width / $this->height;

                if ($width / $height > $ratio) {
                    $height = $this->height * $width / $this->width;
                } else {
                    $width = $this->width * $height / $this->height;
                }
                break;
        }

        // Convert the width && height to integers, minimum value is 1px
        $width = max(round($width), 1);
        $height = max(round($height), 1);

        return [$width, $height];
    }

    /**
     * Normalises width and height before cropping
     *
     * @param   integer $width new width
     * @param   integer $height new height
     * @param   mixed $offsetX offset from the left
     * @param   mixed $offsetY offset from the top
     * @return  $this
     */
    public function beforeCrop($width, $height, $offsetX = 0, $offsetY = 0)
    {
        $width = ($width > $this->width) ? $this->width : $width;
        $height = ($height > $this->height) ? $this->height : $height;

        if ($offsetX === null) {
            // Center the X offset
            $offsetX = round(($this->width - $width) / 2);
        } elseif ($offsetX === true) {
            // Bottom the X offset
            $offsetX = $this->width - $width;
        } elseif ($offsetX < 0) {
            // Set the X offset from the right
            $offsetX = $this->width - $width + $offsetX;
        }

        if ($offsetY === 0) {
            // Center the Y offset
            $offsetY = round(($this->height - $height) / 2);
        } elseif ($offsetY === true) {
            // Bottom the Y offset
            $offsetY = $this->height - $height;
        } elseif ($offsetY < 0) {
            // Set the Y offset from the bottom
            $offsetY = $this->height - $height + $offsetY;
        }

        // Determine the maximum possible width && height
        $max_width = $this->width - $offsetX;
        $max_height = $this->height - $offsetY;

        // Use the maximum available width
        $width = ($width > $max_width) ? $max_width : $width;
        // Use the maximum available height
        $height = ($height > $max_height) ? $max_height : $height;

        return [$width, $height, $offsetX, $offsetY];
    }

    /**
     * Normalises width and height
     *
     * @param   integer $degrees degrees to rotate: -360-360
     * @return  $this
     */
    public function beforeRotate($degrees)
    {
        // Make the degrees an integer
        $degrees = (int)$degrees;

        if ($degrees > 180) {
            do {
                // Keep subtracting full circles until the degrees have normalized
                $degrees -= 360;
            } while ($degrees > 180);
        }

        if ($degrees < -180) {
            do {
                // Keep adding full circles until the degrees have normalized
                $degrees += 360;
            } while ($degrees < -180);
        }

        return [$degrees];
    }

    /**
     * Prepares for image flip
     *
     * @param   integer $direction direction: ResizingConstraint::HORIZONTAL, ResizingConstraint::VERTICAL
     * @return  $this
     */
    public function beforeFlip($direction)
    {
        if ($direction !== ResizingConstraint::HORIZONTAL) {
            // Flip vertically
            $direction = ResizingConstraint::VERTICAL;
        }

        return [$direction];
    }

    /**
     * Prepares for image sharpen
     *
     * @param   integer $amount amount to sharpen: 1-100
     * @return  $this
     */
    public function beforeSharpen($amount)
    {
        // The amount must be in the range of 1 to 100
        $amount = min(max($amount, 1), 100);

        return [$amount];
    }

    /**
     * Normalises before performing image reflection
     *
     * @param   integer $height reflection height
     * @param   integer $opacity reflection opacity: 0-100
     * @param   boolean $fadeIn true to fade in, false to fade out
     * @return  $this
     */
    public function beforeReflection($height = null, $opacity = 100, $fadeIn = false)
    {
        if ($height === null || $height > $this->height) {
            // Use the current height
            $height = $this->height;
        }

        // The opacity must be in the range of 0 to 100
        $opacity = min(max($opacity, 0), 100);

        return [$height, $opacity, $fadeIn];
    }

    /**
     * Normalise before watermark
     *
     * @param   Image $watermark watermark Image instance
     * @param   integer $offsetX offset from the left
     * @param   integer $offsetY offset from the top
     * @param   integer $opacity opacity of watermark: 1-100
     * @return  $this
     */
    public function beforeWatermark(Image $watermark, $offsetX = null, $offsetY = null, $opacity = 100)
    {
        if ($offsetX === null) {
            // Center the X offset
            $offsetX = round(($this->width - $watermark->width) / 2);
        } elseif ($offsetX === true) {
            // Bottom the X offset
            $offsetX = $this->width - $watermark->width;
        } elseif ($offsetX < 0) {
            // Set the X offset from the right
            $offsetX = $this->width - $watermark->width + $offsetX;
        }

        if ($offsetY === null) {
            // Center the Y offset
            $offsetY = round(($this->height - $watermark->height) / 2);
        } elseif ($offsetY === true) {
            // Bottom the Y offset
            $offsetY = $this->height - $watermark->height;
        } elseif ($offsetY < 0) {
            // Set the Y offset from the bottom
            $offsetY = $this->height - $watermark->height + $offsetY;
        }

        // The opacity must be in the range of 1 to 100
        $opacity = min(max($opacity, 1), 100);

        return [$watermark, $offsetX, $offsetY, $opacity];
    }

    /**
     * Normalizes before background
     *
     * @param   string $color hexadecimal color value
     * @param   integer $opacity background opacity: 0-100
     * @return  $this
     */
    public function beforeBackground($color, $opacity = 100)
    {
        if ($color[0] === '#') {
            // Remove the pound
            $color = substr($color, 1);
        }

        if (strlen($color) === 3) {
            // Convert shorth&& into longh&& hex notation
            $color = preg_replace('/./', '$0$0', $color);
        }

        // Convert the hex into RGB values
        list ($red, $green, $blue) = array_map('hexdec', str_split($color, 2));

        // The opacity must be in the range of 0 to 100
        $opacity = min(max($opacity, 0), 100);

        return compact('red', 'green', 'blue', 'opacity');
    }

    /**
     * Normalises before saving
     *
     * @param   string $filePath new image path
     * @param   integer $quality quality of image: 1-100
     * @return  array
     * @throws  \Exception
     */
    public function beforeSave($filePath = null, $quality = 100)
    {
        if ($filePath === null) {
            // Overwrite the file
            $filePath = $this->filePath;
        }

        if (is_file($filePath)) {
            if (!is_writable($filePath)) {
                throw new Exception('File must be writable: :file');
            }
        } else {
            // Get the directory of the file
            $directory = realpath(pathinfo($filePath, PATHINFO_DIRNAME));

            if (!is_dir($directory) || !is_writable($directory)) {
                throw new Exception('Directory must be writable: :directory');
            }
        }

        // The quality must be in the range of 1 to 100
        $quality = min(max($quality, 1), 100);

        return [$filePath, $quality];
    }

    /**
     * Normalize before rendering
     *
     * @param   string $type image type to return: png, jpg, gif, etc
     * @param   integer $quality quality of image: 1-100
     * @return  string
     */
    public function beforeRender($type = null, $quality = 100)
    {
        if ($type === null) {
            // Use the current image type
            $type = image_type_to_extension($this->fileExtension, false);
        }

        return [$type, $quality];
    }

}

