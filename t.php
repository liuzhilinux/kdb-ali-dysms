<?php
/**
 * Created by PhpStorm.
 * User: liuzhilinux
 * Date: 2019-11-27
 * Time: 18:40
 */

require_once 'YunpianSms.php';

$cfg = json_decode(file_get_contents('.cfg'), true);

try {
    $apikey = $cfg['apikey'];

    $sms = new YunpianSms($apikey);

    $res = $sms->tplSingleSend('13812341234', 3353636, ['code' => 'abcd']);

    var_dump($res);
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}


