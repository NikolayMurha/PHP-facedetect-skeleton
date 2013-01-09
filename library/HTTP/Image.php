<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nikolay
 * Date: 03.01.13
 * Time: 17:34
 * To change this template use File | Settings | File Templates.
 */

/**
 * @property int $width
 * @property int $height
 * @property string $type
 * @property string $mime
 * @property mixed $data
 */
class HTTP_Image
{

    private $width;

    private $height;

    private $type;

    private $mime;

    private $data;

    public function __construct($data)
    {
        $this->data = imagecreatefromstring($data);
        list($this->width, $this->height, $this->type, $this->mime) = $this->_getImageSize($data);
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return;
    }

    public function __set($name, $value)
    {
        if ($name == 'data') {
            imagedestroy($this->data);
            $this->data = $value;
            $this->width = imagesx($value);
            $this->height = imagesy($value);
        }
    }

    protected function _getImageSize($data)
    {

        $tmp = tempnam(sys_get_temp_dir(), 'ir');
        file_put_contents($tmp, $data); // Unfortunately for jpeg - we HAVE to save ALL data, else we cannot detect image size.
        list ($w, $h, $type) = getimagesize($tmp);
        $mime = image_type_to_mime_type($type);
        unlink($tmp);
        return array($w, $h, $type, $mime);
    }

    public function save($format = 'png', $filename = null, $quality = null)
    {
        if (!$filename) {

        }
        $func = "image" . $format;
        if (isset($quality) && $quality >= 0 && ($format == "jpeg" && $quality <= 100 || $format == "png" && $quality <= 9)) {
            $func($this->data, $filename, $quality);
        } else {
            $func($this->data, $filename);
        }
    }

    public function send($format = 'png', $quality = null) {
        header("Content-Type: image/".$format);
        // Cache forever, because file content never changes without ID change.
        // Use constant Last-Modified date in the past to ensure that it is NEVER changed
        // (seems WebKit has an unstable bug when it processes Not Modified response
        // status which has different Last-Modified than the original cached content).
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", 3600 * 24 * 365) . " GMT");
        header("Expires: Wed, 08 Jul 2037 22:53:52 GMT");
        header("Cache-Control: public");
        $this->save($format, null, $quality);
    }
}