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
    $sms = new AliDysms();

    $sms->send('13812341234', 'SMS_153055065', '{"code":"1111"}');
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}


