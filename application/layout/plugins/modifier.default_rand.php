<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nik
 * Date: 29.06.12
 * Time: 16:12
 */

function smarty_modifier_default_rand($string)
{

    if (!empty($string)) {
        return $string;
    }
    $args = func_get_args();
    $args = array_slice($args, 1);

    return $args[array_rand($args)];

}