<?php
/**
 * Created by PhpStorm.
 * User: liuzhilinux
 * Date: 2019-11-27
 * Time: 18:40
 */

require_once 'AliDysms.php';

$cfg = json_decode(file_get_contents('.cfg'), true);

try {
    $sms = new AliDysms($cfg['accessKeyId'], $cfg['accessKeySecret']);


} catch (Exception $exception) {

}


