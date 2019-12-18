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

    // 指定内容单发短信。
    $mobile = '13812341234';
    $content = '【快点办】短信内容。';
    $res = $sms->send($mobile, $content);

    // 指定内容群发短信。
    $mobile = '13812341234,15812341234';
    $content = '【快点办】短信内容。';
    $res = $sms->send($mobile, $content);

    // 或者手机号码传入数组。
    $mobile = ['13812341234', '15812341234'];
    $content = '【快点办】短信内容。';
    $res = $sms->send($mobile, $content);

    // 指定模板单发。
    $mobile = '13812341234';
    $tpl_id = 1234567;
    $res = $sms->send($mobile, $tpl_id);
    // 带参数
    $res = $sms->send($mobile, $tpl_id, ['key' => 'val']);

    // 指定模板群发。
    $mobile = ['13812341234', '15812341234'];
    $tpl_id = 1234567;
    $res = $sms->send($mobile, $tpl_id, ['key' => 'val']);

    var_dump($res);
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}


