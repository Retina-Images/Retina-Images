Retina Images
=============

Retina Images serves different images based on the device being used by the viewer.

Once set up on your website (very simple!) all you have to do is create a high-res version of each image you would like optimised for retina screens and all the work is done for you. You don’t even need to change any `<img>` tags (providing they have a height or width).

How it Works
------------

1. When the page loads on the viewers device, a cookie holding the `devicePixelRatio` is set by either JavaScript or CSS (if JS is disabled).
2. When an image is requested by the server, the `.htaccess` file tells `retinaimages.php` to serve the image instead.
3. `retinaimages.php` then checks for the following conditions:
    - A cookie holding `devicePixelRatio` exists.
    - The value set in the cookie is greater than 1.
    -  A high-res version of the image exists.
5. If any of the above are false, it will send the regular image. Otherwise, the high-res image is sent in its place.

Installation & Examples
-----------------------

Visit [http://retina-images.complexcompulsions.com](http://retina-images.complexcompulsions.com) for installation instructions and a set of demonstrations.

Benfits
-------

- Only one image is downloaded by the viewer.
- All standard raster images (.jpg, .png, .gif & .bmp) are able to be served as high-res.
- Fallback to regular image if high-res image isn’t available.
- Fallback to regular image if JavaScript and CSS or Cookies are disabled.

Drawbacks
---------

- `<img>` tags must have either a width or height attribute specified.
- CSS background images must have a background-size property. See [CSS Images](http://retina-images.complexcompulsions.com#setupcss) for details.

License
-------

Retina Images by [Jeremy Worboys](http://jeremyworboys.com) is licensed under a [Creative Commons Attribution 3.0 Unported License](http://creativecommons.org/licenses/by/3.0/)
