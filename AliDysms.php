<?php

/**
 * 阿里云 - 短信服务功能模块。
 * Class AliDysms
 */

class AliDysms
{
    /**
     * @var string $accessKeyId 访问密钥 ID 。
     */
    private $accessKeyId;

    /**
     * @var string $accessKeySecret 访问密钥。
     */
    private $accessKeySecret;

    /**
     * 支持的 API 列表：
     *   短信发送接口：
     *     SendSms            发送短信。
     *     SendBatchSms       批量发送短信。
     *   短信查询接口：
     *     QuerySendDetails   查询短信发送的状态。
     *   签名申请接口：
     *     AddSmsSign         调用短信 AddSmsSign 申请短信签名。
     *     DeleteSmsSign      调用接口 DeleteSmsSign 删除短信签名。
     *     QuerySmsSign       调用接口 QuerySmsSign 查询短信签名申请状态。
     *     ModifySmsSign      调用接口 ModifySmsSign 修改未审核通过的短信签名，并重新提交审核。
     *   模板申请接口：
     *     ModifySmsTemplate  调用接口 ModifySmsTemplate 修改未通过审核的短信模板。
     *     QuerySmsTemplate   调用接口 QuerySmsTemplate 查询短信模板的审核状态。
     *     AddSmsTemplate     调用接口 AddSmsTemplate 申请短信模板。
     *     DeleteSmsTemplate  调用接口 DeleteSmsTemplate 删除短信模板。
     *   回执消息：
     *     SmsReport          订阅 SmsReport 短信状态报告，获取短信发送状态。
     *     SmsUp              订阅 SmsUp 上行短信消息，获取终端用户回复短信的内容。
     *     SignSmsReport      订阅签名审核状态消息（ SignSmsReport ），获取指定签名的审核状态。
     *     TemplateSmsReport  订阅模板审核状态消息（ TemplateSmsReport ），获取指定模板的审核状态。
     *
     * @var string $action API 的名称。
     */
    private $action;

    /**
     * @var array $endpoints 阿里云公网服务地址。
     */
    private $endpoints = [
        'dysmsapi' => [
            'global' => 'dysmsapi.aliyuncs.com',
            'cn-hangzhou' => 'dysmsapi.aliyuncs.com',
            'ap-southeast-1' => 'dysmsapi.ap-southeast-1.aliyuncs.com',
        ],
        'dybaseapi' => [
            'global' => 'dybaseapi.aliyuncs.com',
            'cn-hangzhou' => '1943695596114318.mns.cn-hangzhou.aliyuncs.com',  // http or https;
        ],
    ];

    /**
     * @var string $date_time_format 默认时间格式。
     */
    private $dateTimeFormat = 'Y-m-d\TH:i:s\Z';

    /**
     * @var string $signName 签名。
     */
    private $signName;

    /**
     * @var string $verifyPhoneTemplateCode 短信验证码对应的短信模板 ID 。
     */
    private $verifyPhoneTemplateCode;

    /**
     * @var string $verifyPhoneTemplateField 短信模板变量中的验证码变量名。
     */
    private $verifyPhoneTemplateField;

    /**
     * @var array $baseParams 公共请求参数。
     */
    private $baseParams = [
        'AccessKeyId' => null,
        'Action' => null,
        'Format' => 'json',
        'RegionId' => 'cn-hangzhou',
        'SignatureMethod' => 'HMAC-SHA1',
        'SignatureNonce' => null,
        'SignatureVersion' => '1.0',
        'Timestamp' => null,
        'Version' => '2017-05-25',
    ];

    /**
     * @var array $options 请求参数。
     */
    private $options = [];

    /**
     * AliDysms constructor.
     *
     * @param string $access_key_id     访问密钥 ID 。
     * @param string $access_key_secret 访问密钥。
     */
    public function __construct($access_key_id = null, $access_key_secret = null)
    {
        // 在这里读取配置文件，初始化配置。
        // $this->signName = '';
        // $this->accessKeyId = '';
        // $this->accessKeySecret = '';
        // $this->verifyPhoneTemplateCode = '';
        // $this->verifyPhoneTemplateField = '';

        if ($access_key_id) {
            $this->accessKeyId = $access_key_id;
        }

        if ($access_key_secret) {
            $this->accessKeySecret = $access_key_secret;
        }
    }

    /**
     * 设置 API 的名称。
     *
     * @param string $action API 的名称。
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * 设置参数。
     *
     * @param string $key  参数名。
     * @param mixed $value 参数值。
     *
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * 查看是否存在某参数。
     *
     * @param string $key 参数名。
     *
     * @return bool
     */
    public function hasOption($key)
    {
        return isset($this->options[$key]);
    }

    /**
     * 批量设置参数。
     *
     * @param array $options 参数数组。
     * @param bool $cover    是否覆盖。
     *
     * @return $this
     */
    public function setOptions($options, $cover = false)
    {
        if ($cover) {
            foreach ($options as $k => $v) {
                $this->options[$k] = $v;
            }
        } else {
            $this->options = array_merge($this->options, $options);
        }

        return $this;
    }

    /**
     * 执行请求。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function execute()
    {
        $url = $this->endpoints['dysmsapi']['cn-hangzhou'];
        $baseParams = $this->baseParams;

        $baseParams['AccessKeyId'] = $this->accessKeyId;
        $baseParams['Action'] = $this->action;
        $baseParams['SignatureNonce'] = md5(uniqid(mt_rand(), true));
        $baseParams['Timestamp'] = gmdate($this->dateTimeFormat);

        // 如果请求参数中包含有公共参数中的字段，则保留请求参数中的字段。
        $options = array_merge($this->options, $baseParams);

        unset($options['Signature']);

        $options['Signature'] = $this->computeSignature($options);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->parsePostHttpBody($options));

        curl_setopt($ch, CURLOPT_TIMEOUT, 80);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $http_headers = [
            'Date:' . gmdate($this->dateTimeFormat),
            'Accept:application/json',
            'x-acs-signature-method:HMAC-SHA1',
            'x-acs-signature-version:1.0',
            'x-acs-region-id:cn-hangzhou',
            'x-sdk-client:php/2.0.0',
            'Content-MD5:' . base64_encode(md5(json_encode($options), true)),
            // 'Content-Type:application/octet-stream;charset=utf-8',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);

        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($errno > 0) {
            throw new \Exception($error, $errno);
        }

        $res = json_decode($res, true);

        if (200 != $status || 'OK' != $res['Code']) {
            throw new \Exception('ERR[' . $res['Code'] . ']: ' . $res['Message']);
        }

        // 重置请求参数，保证后续再次使用不会遗留前次的参数。
        $this->options = [];

        return $res;
    }

    /**
     * 发送短信。
     *
     * @param array|string $phone_numbers 手机号码，支持多个号码，多个号码字符串以英文半角逗号（ , ）隔开，支持数组。
     * @param string $template_code       短信模板 ID 。
     * @param string/array|null $templateParam 短信模板变量对应的实际值，支持 json 字符串，如果传入数组，则进行 json 编码。
     * @param string $sign_name           短信签名名称。
     * @param null $out_id                外部流水扩展字段。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function send($phone_numbers, $template_code, $template_param = null, $sign_name = null, $out_id = null)
    {
        $this->setAction('SendSms');

        if (is_array($phone_numbers)) {
            $phone_numbers = join(',', $phone_numbers);
        }

        $this->setOptions([
            'PhoneNumbers' => $phone_numbers,
            'TemplateCode' => $template_code,
        ]);

        if ($sign_name) {
            $this->setOption('SignName', $sign_name);
        }

        if (!$this->hasOption('SignName')) {
            $this->setOption('SignName', $this->signName);
        }

        if (is_string($template_param)) {
            $this->setOption('TemplateParam', $template_param);
        } elseif (is_array($template_param)) {
            $this->setOption('TemplateParam', json_encode($template_param, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        if (is_string($out_id)) {
            $this->setOption('OutId', $out_id);
        }

        return $this->execute();
    }

    /**
     * 发送短信验证码。
     *
     * @param string $phone_number  手机号码。
     * @param int $digit            短信验证码位数，默认 6 位。
     * @param string $verify_code   验证码。
     * @param string $template_code 短信模板 id 。
     * @param  string $field        验证码参数字段。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function sendVerifyCode($phone_number, $digit = 6, $verify_code = null, $template_code = null, $field = null)
    {
        // 生成指定位数的短信验证码。
        if (empty($verify_code)) {
            $verify_code = '';

            for ($i = 0; $i < intval($digit); $i++) $verify_code .= mt_rand(0, 9);
        }

        $template_code = empty($template_code) ? $this->verifyPhoneTemplateCode : $template_code;
        $field = empty($field) ? $this->verifyPhoneTemplateField : $field;

        $response = $this->send(
            $phone_number,
            $template_code,
            [$field => $verify_code]
        );

        if ($response['code'] === 'OK' && $response['message'] === 'OK') {
            $response['phone_number'] = $phone_number;
            $response['verify_code'] = $verify_code;
            return $response;
        }

        return false;
    }

    /**
     * 查询短信发送记录和发送状态。
     *
     * @param string $phone_number 手机号码。
     * @param string $send_date    日期格式，格式为 yyyyMMdd ，例如 20181225 ，可查询最近 30 天内的记录。
     * @param int $current_page    当前页码。
     * @param int $page_size       每页记录数量，取值范围为 1~50 。
     * @param null|string $biz_id  发送回执 ID 。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function getDetails($phone_number, $send_date, $current_page = 1, $page_size = 10, $biz_id = null)
    {
        $this->setAction('QuerySendDetails');

        $this->setOptions([
            'CurrentPage' => $current_page,
            'PageSize' => $page_size,
            'PhoneNumber' => $phone_number,
            'SendDate' => $send_date,
        ]);

        if ($biz_id) {
            $this->setOption('BizId', $biz_id);
        }

        return $this->execute();
    }

    /**
     * 编辑短信签名。
     *
     * @param string $sign_name     签名名称。
     * @param int $sign_source      签名来源。
     * @param string $remark        短信签名申请说明。
     * @param array $sign_file_list 签名的证明文件。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    private function editSign($sign_name, $sign_source, $remark, $sign_file_list = [])
    {
        $this->setOptions([
            'SignName' => $sign_name,
            'SignSource' => $sign_source,
            'Remark' => $remark,
        ]);

        if (is_array($sign_file_list)) {
            $sign_file_list = array_values($sign_file_list);

            foreach ($sign_file_list as $idx => $sign_file) {
                $this->setOption('SignFileList.' . ($idx + 1) . '.FileSuffix', $sign_file['file_suffix']);
                $this->setOption('SignFileList.' . ($idx + 1) . '.FileContents', $sign_file['file_contents']);
            }
        }

        return $this->execute();
    }

    /**
     * 申请短信签名。
     *
     * @param string $sign_name     签名名称。
     * @param int $sign_source      签名来源。其中：
     *
     *                                      0：企事业单位的全称或简称。
     *                                      1：工信部备案网站的全称或简称。
     *                                      2：APP 应用的全称或简称。
     *                                      3：公众号或小程序的全称或简称。
     *                                      4：电商平台店铺名的全称或简称。
     *                                      5：商标名的全称或简称
     *
     * @param string $remark        短信签名申请说明。
     * @param array $sign_file_list 签名的证明文件。以数组形式传入，格式如下：
     *
     *    $sign_file_list = [
     *       ['file_suffix' => 'jpg','file_contents' => 'R0lGOD...iwAA'],
     *       ['file_suffix' => 'jpg','file_contents' => 'R0lGOD...iwAA'],
     *       ['file_suffix' => 'jpg','file_contents' => 'R0lGOD...iwAA'],
     *       ['file_suffix' => 'jpg','file_contents' => 'R0lGOD...iwAA'],
     *    ];
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function addSign($sign_name, $sign_source, $remark, $sign_file_list = [])
    {
        $this->setAction('AddSmsSign');
        return $this->editSign($sign_name, $sign_source, $remark, $sign_file_list);
    }

    /**
     * 删除短信签名。
     *
     * @param string $sign_name 短信签名。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function deleteSign($sign_name)
    {
        $this->setAction('DeleteSmsSign');
        $this->setOption('SignName', $sign_name);
        return $this->execute();
    }

    /**
     * 修改未审核通过的短信签名，并重新提交审核。
     *
     * 参数格式参考 addSign 方法。
     *
     * @param string $sign_name     签名名称。
     * @param int $sign_source      签名来源。
     * @param string $remark        短信签名申请说明。
     * @param array $sign_file_list 签名的证明文件。
     *
     * @return bool|mixed|string
     * @throws Exception
     */
    public function modifySign($sign_name, $sign_source, $remark, $sign_file_list = [])
    {
        $this->setAction('ModifySmsSign');
        return $this->editSign($sign_name, $sign_source, $remark, $sign_file_list);
    }

    /**
     * 查询短信签名申请状态。
     *
     * @param string $sign_name 短信签名名称。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function getSign($sign_name)
    {
        $this->setAction('QuerySmsSign');
        $this->setOption('SignName', $sign_name);
        return $this->execute();
    }

    /**
     * 编辑短信模板。
     *
     * @param string $template_name    模板名称。
     * @param int $template_type       短信类型。
     * @param string $template_content 模板内容。
     * @param string $remark           短信模板申请说明。
     * @param string $template_code    短信模板 CODE 。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    private function editTemplate($template_name, $template_type, $template_content, $remark, $template_code = null)
    {
        $this->setOptions([
            'TemplateName' => $template_name,
            'TemplateType' => intval($template_type),
            'TemplateContent' => $template_content,
            'Remark' => $remark,
        ]);

        if ($template_code) {
            $this->setOption('TemplateCode', $template_code);
        }

        return $this->execute();
    }

    /**
     * 申请短信模板。
     *
     * @param string $template_name    模板名称。
     * @param int $template_type       短信类型。其中：
     *
     *                                  0：验证码。
     *                                  1：短信通知。
     *                                  2：推广短信。
     *                                  3：国际/港澳台消息。
     *
     * @param string $template_content 模板内容，长度为 1~500 个字符。
     * @param string $remark           短信模板申请说明。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function addTemplate($template_name, $template_type, $template_content, $remark)
    {
        $this->setAction('AddSmsTemplate');
        return $this->editTemplate($template_name, $template_type, $template_content, $remark);
    }

    /**
     * 删除短信模板。
     *
     * @param string $template_code 短信模板 CODE 。
     *
     * @return bool|mixed|string
     * @throws Exception
     */
    public function deleteTemplate($template_code)
    {
        $this->setAction('DeleteSmsTemplate');
        $this->setOption('TemplateCode', $template_code);
        return $this->execute();
    }

    /**
     * 修改未通过审核的短信模板。
     *
     * 参数格式参考 addTemplate 方法。
     *
     * @param string $template_code    短信模板 CODE 。
     * @param string $template_name    模板名称。
     * @param int $template_type       短信类型。
     * @param string $template_content 模板内容，长度为 1~500 个字符。
     * @param string $remark           短信模板申请说明。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function modifyTemplate($template_code, $template_name, $template_type, $template_content, $remark)
    {
        $this->setAction('ModifySmsTemplate');
        return $this->editTemplate($template_name, $template_type, $template_content, $remark, $template_code);
    }

    /**
     * 查询短信模板的审核状态。
     *
     * @param string $template_code 短信模板 CODE 。
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function getTemplate($template_code)
    {
        $this->setAction('QuerySmsTemplate');
        $this->setOption('TemplateCode', $template_code);
        return $this->execute();
    }

    /**
     * 根据 POP 规则对要签名的字符串进行编码。
     *
     * @param string $str 要编码的字符串。
     *
     * @return string|string[]|null 已编码的字符串。
     */
    private function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    /**
     * 计算签名。
     *
     * @param array $params 请求参数。
     *
     * @return string 签名。
     */
    private function computeSignature($params)
    {
        global $access_secret;

        ksort($params);

        $sourceArr = [];

        foreach ($params as $k => $v) {
            $sourceArr[] = $this->percentEncode($k) . '=' . $this->percentEncode($v);
        }

        $source = join('&', $sourceArr);

        $source = 'POST' . '&' . $this->percentEncode('/') . '&' . $this->percentEncode($source);

        return base64_encode(hash_hmac('sha1', $source, $this->accessKeySecret . '&', true));
    }

    /**
     * 拼接请求体。
     *
     * @param array $params 请求参数。
     *
     * @return string 请求体字符串。
     */
    private function parsePostHttpBody($params)
    {
        $bodyArr = [];

        foreach ($params as $k => $v) {
            $bodyArr[] = $k . '=' . urlencode($v);
        }

        return join('&', $bodyArr);
    }
}

