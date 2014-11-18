<?php
namespace grooveround\image\drivers;

use grooveround\image\helpers\ResizingConstraint;

/**
 * Interface ImageDriver
 * @package grooveround\image\drivers
 * @author Deick Fynn <dcfynn@vodamail.co.za>
 */
interface ImageDriver
{
    /**
     * Resize the image to the given size. Either the width or the height can
     * be omitted && the image will be resize proportionally.
     *
     * usage:
     *     Resize to 100 pixels on the shortest side
     *     $image->resize(100, 100, ResizingConstraint::AUTO);
     *
     *     Resize to 100x100 pixels, keeping aspect ratio
     *     $image->resize(100, 100, ResizingConstraint::INVERSE);
     *
     *     Resize to 900 pixel width, keeping aspect ratio
     *     $image->resize(600, null, ResizingConstraint::AUTO);
     *
     *     Resize to 600 pixel height, keeping aspect ratio
     *     $image->resize(null, 600, ResizingConstraint::AUTO);
     *
     *     Resize to 200x500 pixels, ignoring aspect ratio
     *     $image->resize(200, 500, ResizingConstraint::NONE);
     *
     * @param int $width
     * @param int $height
     * @param int $constrain
     * @return mixed
     */
    public function resize($width = 0, $height = 0, $constrain = ResizingConstraint::AUTO);

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
     * @param   int $width new width
     * @param   int $height new height
     * @param   int $offsetX offset from the left
     * @param   int $offsetY offset from the top
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
     * @param   int $degrees degrees to rotate
     * @return  bool
     */
    public function rotate($degrees);

    /**
     * Flip the image along the horizontal or vertical axis.
     *
     * usage:
     *     Flip the image from top to bottom
     *     $image->flip(ResizingConstraint::HORIZONTAL);
     *
     *     Flip the image from left to right
     *     $image->flip(ResizingConstraint::VERTICAL);
     *
     * @param   int $direction direction to flip
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
     * @param   int $amount amount to sharpen
     * @return  void
     */
    public function sharpen($amount);

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
     * @param   int $height reflection height
     * @param   int $opacity reflection opacity
     * @param   boolean $fadeIn TRUE to fade out, FALSE to fade in
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
     * @param   Image $image watermarking Image
     * @param   int $offsetX offset from the left
     * @param   int $offsetY offset from the top
     * @param   int $opacity opacity of watermark
     * @return  void
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
     * @param   int $red red channel
     * @param   int $green green channel
     * @param   int $blue blue channel
     * @param   int $opacity opacity
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
     * @param   string $filePath new image filename
     * @param   int $quality quality
     * @return  boolean
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
     * @param   string $type image type: png, jpg, gif, etc
     * @param   int $quality quality
     * @return  string
     */
    public function render($type, $quality);

}