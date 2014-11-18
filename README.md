Image Manager for PHP using GD, Imagick, etc
=======================
Usage Example:

Instantiation:
-------------

~~~

    use grooveround\image\drivers\Gd;
    use grooveround\image\drivers\Imagick;
    use grooveround\image\ImageManager;

    $imageManager = new ImageManager();
    $imageManager->addDriver('imagick', new Imagick('/home/derick/Downloads/10286763_654936081227026_6178232705639774364_o.jpg'));
    $imageManager->resizeImage('imagick', 2000, 2000);
    $imageManager->saveImage('imagick', '/home/derick/Downloads/my-image-module.jpg');

~~~


Resize:
------

~~~

    // Resize to 100 pixels on the shortest side
    $image->resize(100, 100, ResizingConstraint::AUTO);

~~~

Crop:
----

~~~

    // Crop the image to 200x200 pixels, from the center
    $image->crop(200, 200);

~~~

Rotate:
-------

~~~

    // Rotate 90% counter-clockwise
    $image->rotate(-90);

~~~


Flip:
-----

~~~

     //Flip the image from top to bottom
     $image->flip(ResizingConstraint::HORIZONTAL);

     //Flip the image from left to right
     $image->flip(ResizingConstraint::VERTICAL);

~~~

So on...

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist grooveround/image "*"
```

or add

```json
"grooveround/image": "*"
```

to the require section of your composer.json.