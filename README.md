# kdb-ali-dysms
> 独立的短信模块，包含阿里云短信、云片短信。

[TOC]

## 开始

AliDysms - 一个轻量的阿里云短信服务对接模块，内部封装有 `Curl` 支持，无需依赖其他模块，只要引入文件即可使用。

如果你现在在使用的 PHP 开发框架支持 `composer` ，那么最理想的方法是直接通过 `composer` 引入阿里云官方提供的最新 SDK ，然后再进一步封装模型层，显然这个模块并不适合你，详情可以参考下面的资源部分。而这个模块存在的意义就是给老平台，比如 Discuz! 、DedeCMS 、 PhinkPHP 3.2.3 、 微擎平台等提供对接阿里云短信服务的支持。

## 资源

### 阿里云 - 短信服务

* 官方文档：[ 短信服务-阿里云 ]( https://help.aliyun.com/product/44282.html, "短信服务-阿里云-官方文档")
* 在线调试：[OpenAPI Explorer]( https://api.aliyun.com/?spm=a2c4g.11186623.2.13.2fe04e6akedeOC#/?product=Dysmsapi&lang=PHP, "OpenAPI Explorer")
* 官方 PHP SDK：[aliyun/openapi-sdk-php-client: Official repository of the Alibaba Cloud Client for PHP]( https://github.com/aliyun/openapi-sdk-php-client, "新版 SDK")
* 旧版 PHP SDK：[ aliyun/aliyun-openapi-php-sdk: Open API SDK for php developers ]( https://github.com/aliyun/aliyun-openapi-php-sdk, "旧版 SDK")

### 云片

* 
* 

## 开始使用

### 基本使用

首先，在使用这个模块之前，你必须确保你的开发 / 生产环境支持：

```
PHP >= 5.4.* (for support short syntax arrays)
Curl Extension
```

其中， PHP 版本要求在 `5.4` 以上是因为模块中的数组定义采用“短数组定义语法”。

在开始使用这个模块前，你需确保能在你的框架中引用这个模块，以下以 `PhinkPHP 3.2.3` 为例，

首先，想好你要把模块拷贝到那个文件夹下，比如：

```
# 注意，这里的模块文件名做了修改，将原本的 .php 后缀改为 .class.php
Application/Common/Model/AliDysms.class.php
```

然后，打开文件，并在类定义的上面添加命名空间：

```php
namespace Common\Model;
```

同时，找到构造方法，补充如下内容：

```php
    public function __construct($access_key_id = null, $access_key_secret = null)
    {
        // 在这里读取配置文件，初始化配置。
        $this->signName = C('ALI_DYSMS.SIGN_NAME');
        $this->accessKeyId = C('ALI_DYSMS.ACCESS_KEY_ID');
        $this->accessKeySecret = C('ALI_DYSMS.ACCESS_KEY_SECRET');
        // $this->verifyPhoneTemplateCode = '';
        // $this->verifyPhoneTemplateField = '';
		
        // ...
    }
```

这样，下次使用这个模块，你就可以像 `$sms = new AliDysms();` 这样开始使用这个模块了。

首先，我们先来发送一条短信练练手：

```php
// 发送一条短信：
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

上述代码中， `setAction` 方法是用来设置动作的，下面是官方 API 支持的所有动作：


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



同时我们可以通过 `setOptions` 方法来设置短信接口的请求参数，当然，你也可以通过 `setOption` （注意少个 s ）设置某个具体的参数，想这样：

```php
$sms->setOption('PhoneNumbers', '13812341234');
$sms->setOption('SignName', '阿里云');
$sms->setOption('TemplateCode', 'SMS_153055065');
```

**注意：**

多次调用 `setOption` 方法对同一个参数进行设置，那么后面设置的参数值将覆盖前面的结果：

```php
// 最终的 'SignName' 值是是 '快点办' 。
$sms->setOption('SignName', '阿里云');
$sms->setOption('SignName', '快点办');
```

但是多次调用 `setOptions` 方法，如果传入的参数已经设置过了，将不会被覆盖，比如：

```php
// 最终的 'SignName' 值是是 '快点办' 。
$sms->setOptions(['SignName' => '快点办']);
$sms->setOptions(['SignName' => '阿里云']);
```

如果想要覆盖之前的参数，那么你可以这样做：

```php
// 最终的 'SignName' 值是是 '快点办' 。
$sms->setOptions(['SignName' => '阿里云']);
$sms->setOptions(['SignName' => '快点办'], true);
```

每个动作都有不同的参数，具体可以参考官方文档，这里不再赘述。



### 进阶使用

当然，还有更简单的使用方法。

让我们一起想一想，发送一条短信这个动作，我们最关注的点是什么？

```
把[什么信息]发送给[那个号码]
```

对了，就是这两个最关键的点，而其余的诸如设置 `AccessKeyId` 和短信签名等过程难免显得累赘多余，如果多次在不同的地方编写这样的业务代码，不仅使原本简单的代码显得复杂了，而且明显降低了代码的可读性。

如果你已经改写了模块的构造器，使得在初始化模块对象的时候加载了配置，那么，调用上述接口可以更简单，代码如下：

```php
try {
    $sms = new AliDysms();

    $sms->send('13812341234', 'SMS_153055065', '{"code":"1111"}');
} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}
```

一样的功能，更简单易读的代码。

同时， `send` 方法支持群发短信，代码如下：

```php
// 逗号分隔的字符串。
$phoneNumbers = '13812341234,1581234234';
$sms->send($phoneNumbers, 'SMS_153055065', '{"code":"1111"}');

// 数组格式。
$phoneNumbers = ['13812341234', '1581234234'];
$sms->send($phoneNumbers, 'SMS_153055065', '{"code":"1111"}');

// 接口请求参数支持数组。
$phoneNumbers = ['13812341234','1581234234'];
$templateParam = ['code' => '1111'];
$sms->send($phoneNumbers, 'SMS_153055065', $templateParam);
```

很明显的看出，上述需求是发送短信验证码，那么这个模块提供了一个专用的方法 `sendVerifyCode` 用来发送短信验证码，代码如下：

```php
// 抓住关注重点 -> 手机号码，具体要发送什么验证码不是你要关注的。
$result = $sms->sendVerifyCode('13812341234');
// 默认发送 6 位验证码，可以自定义验证码位数。
$result = $sms->sendVerifyCode('13812341234', 4);


// 手机号码：
$result['phone_number'];
// 生成的验证码：
$result['verify_code'];
```

就是这么简单。



### 接口返回值

调用接口后，阿里云服务器将默认返回 `JSON` 格式的数据，模块将 `JSON` 格式的数据转换为数组，并原封不动返回，所以模块使用者可以根据业务逻辑，对返回的数据进行处理，或者根据返回的数据判断接口是否调用成功。

比如，当调用接口发送一条短信：

```php
try {
    $sms = new AliDysms();

    $result = $sms->send('13812341234', 'SMS_153055065', '{"code":"1111"}');
    
    if ('OK' === $result) {
        // 表示发送成功。
        $bizId = $result['BizId']; // 保存 BizId 用以查询短信发送回执。
    }
    
} catch (Exception $exception) {
    // 注意，当请求响应的 Code 不是 'OK' 或者向阿里云发送请求后，返回的 HTTP 状态码不是 200 ，
    // 则会抛出异常，开发者可以根据实际情况捕获异常进行进一步的处理。
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}
```



### 查看短信发送状态

发送短信成功后，我们可以把阿里云服务器返回给我们的发送回执 ID （ `BizId` ）保存下来，下次可以用来查询发送回执。

```php
try {
    $sms = new AliDysms();

    // 最简单的方法，给出手机号码和要查询记录的时间，
    // 时间格式： date('Ymd', $timestamp);
    $result = $sms->getDetails('13812341234', '20191127');

    // 其中，$result['TotalCount']表示记录总数，如果记录数量较多，可以分页查询。
    var_dump($result);

    // 下面的例子表示查询第二页记录，每页 50 条。
    // 默认值为第一页，每页 10 条。
    $result = $sms->getDetails('13812341234', '20191127', 2, 50);

	// 如果有保存 BizId ,可以补充在最后，表示查询某个批次的记录。
    $result = $sms->getDetails('13812341234', '20191127', 1, 10, '123456^123');

} catch (Exception $exception) {
    echo 'Code: ', $exception->getCode(), "\n";
    echo 'Error: ', $exception->getMessage(), "\n";
}
```



### 短信签名

#### 申请短信签名

```php
AliDysms::addSign(
    string $sign_name,             // 签名名称
    int $sign_source,              // 签名来源
    string $remark                 // 短信签名申请说明
    [, array $sign_file_list = []] // 签名的证明文件
):array
```

以上参数中，签名来源取值范围：

- 0：企事业单位的全称或简称。
- 1：工信部备案网站的全称或简称。
- 2：APP应用的全称或简称。
- 3：公众号或小程序的全称或简称。
- 4：电商平台店铺名的全称或简称。
- 5：商标名的全称或简称。

特定情况下要上传证明文件，其中，文件格式：

```php
// file_suffix 表示图片格式，支持 ['jpg', 'png', 'gif', 'jpeg'] 。
// file_contents 表示 base64 编码后的图片，每张图片大小限制在 2MB 内。
$sign_file_list = [
   ['file_suffix' => 'jpg','file_contents' => 'R0lGOD...iwAA'],
   ['file_suffix' => 'jpg','file_contents' => 'R0lGOD...iwAA'],
   ['file_suffix' => 'jpg','file_contents' => 'R0lGOD...iwAA'],
   ['file_suffix' => 'jpg','file_contents' => 'R0lGOD...iwAA'],
];
```



#### 删除短信签名

```php
AliDysms::deleteSign(string $sign_name):array
```



#### 修改未审核通过的短信签名，并重新提交审核

```php
// 和申请短信签名的参数一致。
AliDysms::modifySign(string $sign_name, int $sign_source, 
             string $remark[, array $sign_file_list = []]):array
```



#### 查询短信签名申请状态

```php
AliDysms::getSign(string $sign_name):array
```



### 短信模板

#### 申请短信模板

```php
AliDysms::addTemplate(
    string $template_name,      // 模板名称
    int $template_type,         // 短信类型
    string $template_content,   // 模板内容
    string $remark              // 短信模板申请说明
):array
```



#### 删除短信模板

```php
AliDysms::deleteTemplate(
    string $template_code  // 短信模板 CODE
):array
```



#### 修改未通过审核的短信模板

```php
// 第一个参数为短信模板 CODE ，其余参数和申请短信模板的参数一致。
AliDysms::modifyTemplate(
    string $template_code, 
    string $template_name, 
    int $template_type, 
    string $template_content, 
    string $remark
):array
```



#### 查询短信模板的审核状态

```php
AliDysms::getTemplate(string $template_code):array
```





<p style="text-align:center;">Power By <a href="https://www.kuaidianban.com/" title="快点办">快点办</a></p>