<?php
namespace grooveround\image\drivers;

use grooveround\image\helpers\OptimizerConstant;

/**
 * Interface ImageDriver
 * @package grooveround\image\drivers
 * @author Derick Fynn <dcfynn@vodamail.co.za>
 */
interface ImageDriver
{
    /**
     * Resize the image to the given size. Either the width or the height can
     * be omitted && the image will be resize proportionally.
     *
     * usage:
     *     Resize to 100 pixels on the shortest side
     *     $image->resize(100, 100, OptimizerConstant::AUTO);
     *
     *     Resize to 100x100 pixels, keeping aspect ratio
     *     $image->resize(100, 100, OptimizerConstant::INVERSE);
     *
     *     Resize to 900 pixel width, keeping aspect ratio
     *     $image->resize(600, null, OptimizerConstant::AUTO);
     *
     *     Resize to 600 pixel height, keeping aspect ratio
     *     $image->resize(null, 600, OptimizerConstant::AUTO);
     *
     *     Resize to 200x500 pixels, ignoring aspect ratio
     *     $image->resize(200, 500, OptimizerConstant::NONE);
     *
     * @param int $width
     * @param int $height
     * @param int $constrain
     * @return mixed
     */
    public function resize($width = 0, $height = 0, $constrain = OptimizerConstant::AUTO);

    /**
     * Crop an image to the given size. Either the width or the height can be
     * omitted && the current width or height will be used.
     *
     * If no offset is specified, the center of the axis will be used.
     * If an offset of TRUE is specified, the bottom of the axis will be used.
     *
     * usage:
     *     Crop the image to 200x200 pixels, from the center
     *     $image->crop(200, 200);
     *
     * @param int $width
     * @param int $height
     * @param int $offsetX
     * @param int $offsetY
     * @return  void
     */
    public function crop($width, $height, $offsetX = 0, $offsetY = 0);

    /**
     * Rotate the image by a given amount.
     *
     * usage:
     *     Rotate 45 degrees clockwise
     *     $image->rotate(45);
     *
     *     Rotate 90% counter-clockwise
     *     $image->rotate(-90);
     *
     * @param int $degrees
     * @return  bool
     */
    public function rotate($degrees);

    /**
     * Flip the image along the horizontal or vertical axis.
     *
     * usage:
     *     Flip the image from top to bottom
     *     $image->flip(OptimizerConstant::HORIZONTAL);
     *
     *     Flip the image from left to right
     *     $image->flip(OptimizerConstant::VERTICAL);
     *
     * @param int $direction
     * @return  bool
     */
    public function flip($direction);

    /**
     * Sharpen the image by a given amount.
     *
     * usage:
     *     Sharpen the image by 20%
     *     $image->sharpen(20);
     *
     * @param int $value
     * @return  void
     */
    public function sharpen($value);

    /**
     * Add a reflection to an image. The most opaque part of the reflection
     * will be equal to the opacity setting && fade out to full transparent.
     * Alpha transparency is preserved.
     *
     * usage:
     *     Create a 50 pixel reflection that fades from 0-100% opacity
     *     $image->reflection(50);
     *
     *     Create a 50 pixel reflection that fades from 100-0% opacity
     *     $image->reflection(50, 100, true);
     *
     *     Create a 50 pixel reflection that fades from 0-60% opacity
     *     $image->reflection(50, 60, true);
     *
     * note: The reflection will be go from transparent at the top
     * to opaque at the bottom, by default
     *
     * @param int $height
     * @param int $opacity
     * @param bool $fadeIn
     * @return  void
     */
    public function reflection($height, $opacity, $fadeIn);

    /**
     * Add a watermark to an image with a specified opacity. Alpha transparency
     * will be preserved.
     *
     * If no offset is specified, the center of the axis will be used.
     * If an offset of true is specified, the bottom of the axis will be used.
     *
     * usage: TODO: Doc properly
     *     Add a watermark to the bottom right of the image
     *     $mark = new Image('upload/watermark.png');
     *     $image->watermark($mark, true, true);
     *
     * @param Image $image
     * @param int $offsetX
     * @param int $offsetY
     * @param int $opacity
     * @return void
     */
    public function watermark(Image $image, $offsetX, $offsetY, $opacity);

    /**
     * Set the background color of an image. This is only useful for images
     * with alpha transparency.
     *
     * usage:
     *     Make the image background black
     *     $image->background('#00000');
     *
     *     Make the image background black with 50% opacity
     *     $image->background('#00000', 50);
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $opacity
     * @return void
     */
    public function background($red, $green, $blue, $opacity);

    /**
     * Save the image. If the filename is omitted, the original image will
     * be overwritten.
     *
     * usage:
     *     Save the image as a PNG
     *     $image->save('saved/cool.png');
     *
     *     Overwrite the original image
     *     $image->save();
     *
     * @param string $filePath
     * @param int $quality
     * @return  bool
     */
    public function save($filePath = null, $quality);

    /**
     * Render the image && return the binary string.
     *
     * usage
     *     Render the image at 50% quality
     *     $data = $image->render(null, 50);
     *
     *     Render the image as a PNG
     *     $data = $image->render('png');
     *
     * @param string $type
     * @param int $quality
     * @return  string
     */
    public function render($type, $quality);

}