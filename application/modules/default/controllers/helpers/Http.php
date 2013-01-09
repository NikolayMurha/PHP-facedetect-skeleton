<?php

/**
 * @author Bohdan Zhuravel <bohdan.z@randrmusic.com>
 */
class Default_Controller_Action_Helper_Http extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * @param \Zend_Http_Client $client
     * @param string $method
     * @return \Zend_Http_Response
     */
    public function request(\Zend_Http_Client $client, $method = 'GET')
    {
        try {
            $response = $client->request($method);
        } catch (\Zend_Http_Client_Exception $e) {
            // Timeout or host not accessible
            return false;
        }

        if ($response->isError()) {
            // Error in response
            return false;
        }

        return $response;
    }

}