<?php
require_once "HTTP/UrlSigner.php";

/**
 * Resize images "on the fly" or builds a signed URL for an image resize.
 * You MUST use outer caching system (e.g. nginx) to deal with performance.
 *
 * @version 1.02
 */
class HTTP_ImageResizer
{
    private $_urlSigner;
    private $_dataGetter;

    /**
     * Create a new ImageResizer object.
     *
     * @param HTTP_UrlSigner $urlSigner
     * @param callback $dataGetter
     */
    public function __construct(HTTP_UrlSigner $urlSigner, $dataGetter)
    {
        $this->_urlSigner = $urlSigner;
        $this->_dataGetter = $dataGetter;
    }

    /**
     * Front controller method.
     * Process the request and prints resulting image.
     *
     * @param string $requestUrl    URL of the current script. May be absolute or relative.
     * @return void  Never returns.
     */
    public function main($requestUrl)
    {
        try {
            $request = $this->_urlSigner->parseUrl($requestUrl);
        } catch (Exception $e) {
            $this->_error($e->getMessage());
            return;
        }

        $image = $this->getResize($request);
        $image->send($request['format'], @$request['quality']);
    }

    /**
     * Builds URL with data is token mixed in.
     *
     * Input array may contain keys:
     * - "w": width of the new image (required)
     * - "h": height of the new image (required)
     * - "bg": background color in HTML-format (if present, resulting image always
     *   has the size of w*h, and original image is drawed in the center)
     * - "format": gif | jpeg | png (by default - png)
     * - "quality": jpeg or png quality (optional)
     *
     * @param array $request
     * @return string
     */
    public function getUrl(array $request)
    {
        assert('isset($request["width"])');
        assert('isset($request["height"])');
        return $this->_urlSigner->buildUrl($request);
    }

    /**
     * Return resized image data.
     *
     * @param array $request
     * @return HTTP_Image
     */
    public function getResize(array $request)
    {
        $data = call_user_func($this->_dataGetter, $request);
        $image = new HTTP_Image($data);
        $this->_resize($image, $request);
        return $image;
    }

    /**
     * Return URL signer object.
     *
     * @return HTTP_UrlSigner
     */
    public function getSigner()
    {
        return $this->_urlSigner;
    }

    /**
     * Called on error.
     *
     * @param string $msg
     */
    protected function _error($msg)
    {
        header("HTTP/1.1 404 Not Found");
        echo $msg;
    }

    /**
     * @param HTTP_Image $image
     * @param array $request
     * @return array
     */
    private function _resize($image, array $request)
    {

        list($newW, $newH) = $this->_calculate($image, $request);

        /*
            print '<div class="box" style="width:' . $request['width'] . 'px;height:' . $request['width'] . 'px"></div>';
            print '<div class="box2" style="width:' . $newW . 'px;height:' . $newH . 'px"></div>';
            print '<div class="image" style="width:' . $image->width. 'px;height:' . $image->height . 'px"></div>';
        */

        // Resize
        $newIm = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($newIm, $image->data, 0, 0, 0, 0, $newW, $newH, $image->width, $image->height);
        $image->data = $newIm;

        return $this->_entropyCrop($image, $request);

        //Move to canvas
        $canvas = $this->_createCanvas($image->data, $request);
        $x = (imagesx($canvas) - $image->width) / 2;
        $y = (imagesy($canvas) - $image->height) / 2;

        fb::send('$x=' . $x);
        fb::send('$y=' . $y);

        imagecopy($canvas, $image->data, $x, $y, 0, 0, $image->width, $image->height);
        $image->data = $canvas;
        return $image;
    }


     private function _getUriByUrl($url)
    {
        $parsed = parse_url($url);
        return $parsed['path'] . (@$parsed['query'] ? '?' . $parsed['query'] : "");
    }

    /**
     * @param HTTP_Image $image
     * @param array $request
     * @return array
     */
    private function _calculate($image, array $request)
    {
        $maxW = $request['width'];
        $maxH = $request['height'];

        $crop = !empty($request['crop']) ? true : false;
        $fit = !empty($request['fit']) ? true : false;

        $w = $image->width;
        $h = $image->height;
        $ratio = 1;
        if (!$fit && $w < $maxW && $h < $maxH) {
            $ratio = 1;
        } elseif (
            (($h > $maxH || $w > $maxW) && $fit)
            || $w > $maxW && $h > $maxH
            || ($fit && $w < $maxW && $h < $maxH)
        )  {
            $ratio = $crop ?
                max($maxW / $w, $maxH / $h) :
                min($maxW / $w, $maxH / $h);
        }

        $newW = round($w*$ratio);
        $newH = round($h*$ratio);
        return array($newW, $newH);
    }

    protected function _createCanvas($image, $request) {
        $res = imagecreatetruecolor($request['width'], $request['height']);
        $bg = !empty($request['bg']) ? $request['bg'] : false;
        fb::send($bg);

        if ($bg != false && $bg !== 'transparent') {
            fb::send('color');
            $rgb = sscanf(preg_replace('/#/', '', $request['bg']), '%2x%2x%2x');
            $color = imagecolorallocate($res, $rgb[0], $rgb[1], $rgb[2]);
            fb::send($color);
        } elseif (($bg === 'transparent' || $bg === false)
            && ($request['format'] == 'gif' || $request['format'] == 'png')
        ) {
            $color = false;
        } else {
            $color = imagecolorallocate($res, 255, 255, 255); // White as default
        }

        if ($request['format'] == 'gif' && $color === false) {
            // Grab transparent color index from image resource.
            $transparent = imagecolortransparent($image->data);

            if ($transparent >= 0) {
                // The original must have a transparent color, allocate to the new image.
                $transparent_color = imagecolorsforindex($image->data, $transparent);
                $transparent = imagecolorallocate($res, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);

                // Flood with our new transparent color.
                imagefill($res, 0, 0, $transparent);
                imagecolortransparent($res, $transparent);
            }
        } elseif ($request['format'] == 'png' && $color === false) {
            $res = $this->_imageCreateAlpha($request['width'], $request['height']);
        } else {
            imagefill($res, 0, 0, $color);
        }

        return $res;
    }

    protected function _imageCreateAlpha($width, $height)
    {
        // Create a normal image and apply required settings
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        // Apply the transparent background
        $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                imagesetpixel($img, $x, $y, $trans);
            }
        }

        return $img;
    }


    protected function _entropyCrop($image, $request) {
        $data = $image->data;
        $dx = $image->width - min($image->width, $request['width']);
        $dy = $image->height - min($image->height, $request['height']);
        $left = $top = 0;
        $left_entropy = $right_entropy = $top_entropy = $bottom_entropy = 0;
        $right = $image->width;
        $bottom = $image->height;

        // Slice from left and right edges until the correct width is reached.
        while ($dx) {
            $slice = min($dx, 10);

            // Calculate the entropy of the new slice.
            if (!$left_entropy) {
                $left_entropy = $this->_entropySlice($image, $left, $top, $slice, $image->height);
            }
            if (!$right_entropy) {
                $right_entropy = $this->_entropySlice($image, $right - $slice, $top, $slice, $image->height);
            }

            // Remove the lowest entropy slice.
            if ($left_entropy >= $right_entropy) {
                $right -= $slice;
                $right_entropy = 0;
            }
            else {
                $left += $slice;
                $left_entropy = 0;
            }
            $dx -= $slice;
        }

        // Slice from the top and bottom edges until the correct width is reached.
        while ($dy) {
            $slice = min($dy, 10);

            // Calculate the entropy of the new slice.
            if (!$top_entropy) {
                $top_entropy = $this->_entropySlice($image, $left, $top, $request['width'], $slice);
            }
            if (!$bottom_entropy) {
                $bottom_entropy = $this->_entropySlice($image, $left, $bottom - $slice, $request['width'], $slice);
            }

            // Remove the lowest entropy slice.
            if ($top_entropy >= $bottom_entropy) {
                $bottom -= $slice;
                $bottom_entropy = 0;
            }
            else {
                $top += $slice;
                $top_entropy = 0;
            }
            $dy -= $slice;
        }

        // Finally, crop the image using the coordinates found above.
        $cropped_image = $this->_createCanvas($image, array( 'width' => $right - $left, 'height'=> $bottom - $top, 'format'=> 'jpeg' ));
        imagecopy($cropped_image, $image->data, 0, 0, $left, $top, $right - $left, $bottom - $top);
        $image->data = $cropped_image;
        //$image_data->info['width'] = $requested_x;
        //$image_data->info['height'] = $requested_y;
        return true;
    }

    protected function _entropySlice($image,  $x, $y, $width, $height) {
        $slice = $this->_createCanvas($image, array( 'width' => $width, 'height'=> $height, 'format'=> 'jpeg' ));
        imagecopy($slice, $image->data, 0, 0, $x, $y, $width, $height);
        $entropy = $this->_calculateEntropy($slice);
        imagedestroy($slice);
        return $entropy;
    }

    protected function _calculateEntropy($img) {
        $histogram = $this->_histogram($img);
        $histogram_size = array_sum($histogram);
        $entropy = 0;
        foreach ($histogram as $p) {
            if ($p == 0) {
                continue;
            }
            $p = $p / $histogram_size;
            $entropy += $p * log($p, 2);
        }
        return $entropy * -1;
    }

    protected function _histogram($img) {
        $histogram = array_fill(0, 768, 0);
        $w = imagesx($img);
        $h = imagesy($img);
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $rgb = imagecolorat($img, $i, $j);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $histogram[$r]++;
                $histogram[$g + 256]++;
                $histogram[$b + 512]++;
            }
        }
        return $histogram;
    }
}


