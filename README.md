# kdb-ali-dysms
> 独立的阿里云短信模块。

[TOC]

## 开始



## 资源

* 官方文档：[ 短信服务-阿里云 ]( https://help.aliyun.com/product/44282.html, "短信服务-阿里云-官方文档")
* 在线调试：[OpenAPI Explorer]( https://api.aliyun.com/?spm=a2c4g.11186623.2.13.2fe04e6akedeOC#/?product=Dysmsapi&lang=PHP, "OpenAPI Explorer")
* 官方 PHP SDK：[aliyun/openapi-sdk-php-client: Official repository of the Alibaba Cloud Client for PHP]( https://github.com/aliyun/openapi-sdk-php-client, "新版 SDK")
* 旧版 PHP SDK：[ aliyun/aliyun-openapi-php-sdk: Open API SDK for php developers ]( https://github.com/aliyun/aliyun-openapi-php-sdk, "旧版 SDK")

## 开始使用

### 基本使用

最简单的使用：

```php
// 发送短信：
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
```

其中，可以通过 `setAction` 方法设置动作，以下列出官方 API 支持的所有动作：


- 短信发送接口：

     - SendSms  发送短信。
     - SendBatchSms  批量发送短信。
- 短信查询接口：

     - QuerySendDetails  查询短信发送的状态。
- 签名申请接口：

     - AddSmsSign  调用短信 AddSmsSign 申请短信签名。
     - DeleteSmsSign  调用接口 DeleteSmsSign 删除短信签名。
     - QuerySmsSign  调用接口 QuerySmsSign 查询短信签名申请状态。
     - ModifySmsSign  调用接口 ModifySmsSign 修改未审核通过的短信签名，并重新提交审核。
- 模板申请接口：

     - ModifySmsTemplate  调用接口 ModifySmsTemplate 修改未通过审核的短信模板。
     - QuerySmsTemplate  调用接口 QuerySmsTemplate 查询短信模板的审核状态。
     - AddSmsTemplate  调用接口 AddSmsTemplate 申请短信模板。
     - DeleteSmsTemplate  调用接口 DeleteSmsTemplate 删除短信模板。
- 回执消息：

     - SmsReport  订阅 SmsReport 短信状态报告，获取短信发送状态。
     - SmsUp  订阅 SmsUp 上行短信消息，获取终端用户回复短信的内容。
     - SignSmsReport  订阅签名审核状态消息（ SignSmsReport ），获取指定签名的审核状态。
     - TemplateSmsReport  订阅模板审核状态消息（ TemplateSmsReport ），获取指定模板的审核状态。



可以通过 `setOptions` 方法设置短信接口的请求参数，同时，你也可以通过 `setOption` （注意少个 s ）设置某个具体的参数，想这样：

```php
$sms->setOption('PhoneNumbers', '13812341234');
$sms->setOption('SignName', '阿里云');
$sms->setOption('TemplateCode', 'SMS_153055065');
```

如果这样的话，之前已经设定的值将被覆盖。

每个动作都有不同的参数，具体可以参考官方文档。



### 进阶使用

当然，还有更简单的使用方法，如果你已经改写了模块的构造器，使其初始化模块对象的时候加载了配置，那么，调用以上接口可以更简单，代码如下：

```php
try {
    $sms = new AliDysms();

    $sms->send('13812341234', 'SMS_153055065', '{"code":"1111"}');
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}
```

实现一样的功能。

同时， `send` 方法支持群发短信，代码如下：

```php
// 逗号分隔的字符串。
$phoneNumbers = '13812341234,1581234234';
$sms->send($phoneNumbers, 'SMS_153055065', '{"code":"1111"}');

// 数组格式。
$phoneNumbers = ['13812341234','1581234234'];
$sms->send($phoneNumbers, 'SMS_153055065', '{"code":"1111"}');

// 接口请求参数支持数组。
$phoneNumbers = ['13812341234','1581234234'];
$templateParam = ['code' => '1111'];
$sms->send($phoneNumbers, 'SMS_153055065', $templateParam);
```

很明显的看出，上述需求是发送短信验证码，那么这个模块提供了一个专用的方法 `sendVerifyCode` 用来发送短信验证码，代码如下：

```php
$result = $sms->sendVerifyCode('13812341234');

// 手机号码：
$result['phone_number'];
// 生成的验证码：
$result['verify_code'];
```

就是这么简单。



### 查看短信发送状态

```php
try {
    $sms = new AliDysms($cfg['accessKeyId'], $cfg['accessKeySecret']);

    $result = $sms->getDetails('13812341234', '20191127');

    var_dump($result);
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}
```





