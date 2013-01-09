<?php
/**
 * @author Bohdan Zhuravel <bohdan.z@randrmusic.com>
 */
class Default_Controller_Action_Helper_Download extends Zend_Controller_Action_Helper_Abstract
{

    protected $signature = ";qw2vpkqhT7bwJPzLljvvV6nhiJdMdDeijPpXldAcNjtP5kVbZA37NtuLM9hdQ5cRHpPTkC169Fq04QqyEqJHHI2yUMKszdpSliuXRwADQ5bT8";

    protected $liveTime = 604800; //Week
    /**
     * @param string $url
     * @param string $filename
     * @return bool
     */
    public function fromUrl($url, $filename)
    {
        $headers = @get_headers($url, true);
        if (!(is_array($headers) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $headers[0]) : false))
            return false;

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);

        $filename = str_replace(array_merge(
            array_map('chr', range(0, 31)), array('<', '>', ':', '"', '/', '\\\\', '|', '?', '*')
        ), '', html_entity_decode($filename));

        $this->_actionController->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Content-Description', 'File Transfer')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Expires', '0')
            ->setHeader('Cache-Control', 'must-revalidate')
            ->setHeader('Pragma', 'public', true);

        if (isset($headers['Content-Type']))
            $this->_actionController->getResponse()->setHeader('Content-Type', $headers['Content-Type'], true);
        else
            $this->_actionController->getResponse()->setHeader('Content-Type', 'application/octet-stream', true);

        if (isset($headers['Content-Length']))
            $this->_actionController->getResponse()->setHeader('Content-Length', $headers['Content-Length']);

        $this->_actionController->getResponse()->clearBody();
        $this->_actionController->getResponse()->sendHeaders();

        $handle = fopen($url, 'rb');

        if ($handle === false)
            return false;

        while (!feof($handle)) {
            echo fread($handle, 1024 * 1024);
            ob_flush();
            flush();
        }

        return fclose($handle);
    }

    public function makeDownloadLink($trackId, $routerParams = array())
    {

        if (stripos($trackId, 'medianet:') !== false) {
            return false;
        }

        $params = array(
            'expire' => time() + $this->liveTime,
            'trackID' => $trackId,
        );

        $signature = $this->_makeSignature($params);
        $params['signature'] = $signature;
        return $this->_actionController->getHelper('url')->url($routerParams) . '?' . http_build_query($params);
    }

    public function validateRequest()
    {
        $expire = $this->getRequest()->getQuery('expire');
        $signature = $this->getRequest()->getQuery('signature');
        $trackID = $this->getRequest()->getQuery('trackID');

        $params = array(
            'expire' => $expire,
            'trackID' => $trackID,
        );

        if ($signature != $this->_makeSignature($params)) {
            throw new Exception('Signature invalid!');
        }

        if ($expire < time()) {
            throw new Exception('Link is expired!');
        }
        return true;
    }

    protected function _makeSignature($params)
    {
        return md5(http_build_query($params) . $this->signature);
    }
}