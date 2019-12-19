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

    $res = $sms->sendVerifyCode('13812341234');

    // 手机号码。
    $mobile = $res['mobile'];
    // 生成的验证码。
    $verify_code = $res['verify_code'];

    var_dump($res);
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}


