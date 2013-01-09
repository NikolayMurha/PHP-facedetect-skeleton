<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nik
 * Date: 29.06.12
 * Time: 16:12
 */

function smarty_function_json($params)
{
    $output = Zend_Json::encode($params['data']);
    if (isset($params['escape'])) {
        $output = htmlspecialchars($output);
    }
    return $output;
}
