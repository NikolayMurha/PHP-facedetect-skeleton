<?php

class Default_IndexController extends Zend_Controller_Action
{
    /**
     * @var TuneHog_Discovery_Api_Mobile
     */
    protected $signer;

    protected $faceCascades = array(
        'haarcascade_frontalface_default.xml',
        'haarcascade_frontalface_alt2.xml',
        'haarcascade_frontalface_alt_tree.xml',
        'haarcascade_profileface.xml',
        'haarcascade_frontalface_alt.xml',
    );

    protected $faceElementsCascades = array(
        'haarcascade_eye.xml',
        'haarcascade_mcs_mouth.xml',
        'haarcascade_eye_tree_eyeglasses.xml',
        'haarcascade_mcs_eyepair_small.xml',
        'haarcascade_mcs_eyepair_big.xml',
        'haarcascade_mcs_righteye.xml',
        'haarcascade_mcs_lefteye.xml',
        'haarcascade_mcs_nose.xml',
    );


    public function init()
    {
        $this->signer = new HTTP_UrlSigner("very-secret-word",'/default/index/resize/*');
    }

    public function generateAction() {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $xml = simplexml_load_file('http://api.flickr.com/services/feeds/photos_public.gne?tags=face&format=rss_100');

        $files = array();
        $path = realpath(APPLICATION_PATH.'/../public/images/random/');
        // Parse the feed
        foreach($xml->item as $item) {
            preg_match_all('/(alt|title|src)="([^"]*)"/i',$item->description, $result);

            $fileUrl = str_replace('_m.jpg', '_b.jpg', $result[2][1]);
            print $fileUrl.'<br>';
            flush();
            $filename = uniqid().'.jpg';
            $fileData = file_get_contents($fileUrl);
            file_put_contents($path.'/'.$filename,$fileData);
            $files[] = $filename;
            if (count($files) >= 20) break;
        }
    }

    public function indexAction()
    {
        // Get Contents from flickr

        /*$resizer = new HTTP_ImageResizer($this->signer, null);
        $paramsToPassToProcessor = array(
            "height" => 500,
            "width" => 127,
            'fit' => 0,
            'crop' => 1,
            'format' => 'jpeg',
            'quality' => 100,
            'image' => 'sample3.jpg'
        );
        */
        $images = array();
        $file = APPLICATION_PATH.'/../public/images/random/*.jpg';
        $files = glob($file);

        foreach($files as $file) {
            $file = basename($file);
            $images[] = '/default/index/resize/?width=1000&file='.$file;
        }

        shuffle($images);
        $this->view->images = $images;//$resizer->getUrl($paramsToPassToProcessor);

    }

    public function resizeAction()
    {
	    set_time_limit(300);
        $file = APPLICATION_PATH.'/../public/images/random/'.$_GET['file'];
        $file = realpath($file);
        $img = imagecreatefromjpeg($file);


        $maxW = !empty($_GET['width']) ? $_GET['width'] : 0;
        $maxH = !empty($_GET['height']) ? $_GET['height'] : 0;

        list($w, $h) = getimagesize($file);
        $ratio = max($maxW / $w, $maxH / $h);

        $newIm = imagecreatetruecolor(round($w*$ratio), round($h*$ratio));
        imagecopyresampled($newIm, $img, 0, 0, 0, 0, round($w*$ratio), round($h*$ratio), $w, $h);

        $tmp = tempnam(sys_get_temp_dir(), 'ir');
        imagepng($newIm, $tmp);

        $img = $newIm;
        /*
        * haarcascade_frontalface_alt2.xml
        * haarcascade_frontalface_default.xml
        * haarcascade_frontalface_alt_tree.xml
        * haarcascade_profileface.xml
        * haarcascade_frontalface_alt.xml
        *
        * haarcascade_eye.xml
        * haarcascade_mcs_mouth.xml
        * haarcascade_eye_tree_eyeglasses.xml
        * haarcascade_mcs_eyepair_small.xml
        * haarcascade_mcs_eyepair_big.xml
        * haarcascade_mcs_righteye.xml
        * haarcascade_mcs_lefteye.xml
        * haarcascade_mcs_nose.xml

        * haarcascade_mcs_rightear.xml
        * haarcascade_mcs_leftear.xml

        * haarcascade_lefteye_2splits.xml
        * haarcascade_righteye_2splits.xml

        * haarcascade_fullbody.xml
        * haarcascade_lowerbody.xml
        * haarcascade_mcs_upperbody.xml
        * haarcascade_upperbody.xml
        */
        $faces = face_detect($tmp, "/usr/local/share/OpenCV/haarcascades/haarcascade_frontalface_alt.xml", "/usr/local/share/OpenCV/haarcascades/haarcascade_eye_tree_eyeglasses.xml", false );

        if (!$faces) {
            $faces = array();
        }
        $color = imagecolorallocate($newIm, 0, 255, 0);

        foreach($faces AS $face) {
            $color = imagecolorallocate($newIm, 0, 255, 0);
            imagepolygon($newIm,
                array(
                    $face['x'], $face['y'],
                    $face['x'] + $face['w'], $face['y'],
                    $face['x'] + $face['w'], $face['y'] + $face['h'],
                    $face['x'], $face['y'] + $face['h'],
                ),
                4,
                $color);
            $color = imagecolorallocate($newIm, 255, 255, 0);
            foreach($face['eyes'] AS $eye) {

                imagepolygon($newIm,
                    array(
                        $face['x'] + $eye['x'], $face['y'] + $eye['y'],
                        $face['x'] + $eye['x'] + $eye['w'], $face['y'] + $eye['y'],
                        $face['x'] + $eye['x'] + $eye['w'], $face['y'] + $eye['y'] + $eye['h'],
                        $face['x'] + $eye['x'], $face['y'] + $eye['y'] + $eye['h'],
                    ),
                    4,
                    $color);
            }
        }

        header("Content-Type: image/png");
        imagepng($newIm);
        unlink($tmp);
        die;
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);

        $resizer = new HTTP_ImageResizer($this->signer, function ($params) {
            $file = 'images/' . $params['image'];
            var_dump(face_count($file, 'cascade.xml'));
            var_dump(face_detect($file, 'cascade.xml'));

            return file_get_contents($file);
        });

        $resizer->main($_SERVER['REQUEST_URI']);
        die;
    }

    protected  function _resize($file, $maxW, $maxH) {
        list($w, $h) = getimagesize($file);
        $ratio = max($maxW / $w, $maxH / $h);
        $img = imagecreatefromjpeg($file);
        $newIm = imagecreatetruecolor(round($w*$ratio), round($h*$ratio));
        imagecopyresampled($newIm, $img, 0, 0, 0, 0, round($w*$ratio), round($h*$ratio), $w, $h);

        $tmp = tempnam(sys_get_temp_dir(), 'ir');
        imagepng($newIm, $tmp);
        return $tmp;
    }

    public function testAction() {
         ini_set('display_errors', true);
         error_reporting(E_ALL);
        $cascadePath = '/usr/local/share/OpenCV/haarcascades/';
        $statistic = array();
        $file = APPLICATION_PATH.'/../public/images/random/*.jpg';
        $files = glob($file);

        foreach($files as $file) {
            $detections = array();
            for($i = 800; $i <= 1200; $i += 100) {
                $newFile = $this->_resize($file, $i, 0);

                foreach($this->faceCascades AS $faceCascade) {
                    foreach($this->faceElementsCascades AS $faceElement) {
                        $detections[$faceCascade][$faceElement][$i] = face_detect($newFile, $cascadePath.$faceCascade, $cascadePath.$faceElement);
                    }

                }
            }

            flush();
            $this->getHelper('layout')->disableLayout();
            $this->getHelper('viewRenderer')->setNoRender(true);
            $pathinfo = pathinfo($file);
            $statistic = array(
                'file' => $pathinfo['basename'],
                'detections' => $detections
            );

            $tmp = tempnam(sys_get_temp_dir(), 'statistic_');
            file_put_contents($tmp, serialize($statistic));
        }
    }

    public function reportAction() {
        $filePart = sys_get_temp_dir().'/statistic_*';
        $files = glob($filePart);
        print_r($files);

        foreach($files as $file) {
            if (strpos($file, 'html')) {
                continue;
            }
            $fileCnt = unserialize(file_get_contents($file));
            if (isset($fileCnt[0])) {
                $fileCnt = $fileCnt[0];
            }
            ob_start();

            print '<table>';
            print '<th colspan="30">'.$fileCnt['file'].'</th>';
            $head = 0;
            foreach($fileCnt['detections'] as $faceCascade => $elements) {
                foreach($elements as $faceElement => $dimetions) {
                    if (!$head) {
                        print '<td> </td>';
                    }

                    print '<td>'.$faceCascade.' '.$faceElement.'</td>';
                    foreach($dimetions as $w=>$count) {
                        if (!$head) {
                            print '<td>'.$w.'</td>';
                            $head = 1;
                        }
                        print '<td>'.$count.'</td>';
                    }
                }
            }

            print '</table>';
            $table = ob_get_clean();
            file_put_contents($file.'.html', $table);
        }
    }
}

