<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nik
 * Date: 29.06.12
 * Time: 16:12
 */

function smarty_modifier_json($string)
{
    return Zend_Json::encode($string);
}
