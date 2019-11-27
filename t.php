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

    $sms->setAction('SendSms')->setOptions([
        'PhoneNumbers' => '13812341234',
        'SignName' => '阿里云',
        'TemplateCode' => 'SMS_153055065',
        'OutId' => 'abcdefgh',
        'TemplateParam' => '{"code":"1111"}',
    ])->execute();
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}


