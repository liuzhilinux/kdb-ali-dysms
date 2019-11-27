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

    $result = $sms->getDetails('13812341234', '20191127');

    var_dump($result);
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}


