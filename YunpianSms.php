<?php

/**
 * 云片 - 短信模块。
 * Class YunpianSms
 */
class YunpianSms
{
    /**
     * @var string 用户唯一标识。
     */
    private $apikey;
    /**
     * @var string 请求接口的协议，默认 https 。
     */
    private $protocol = 'https';

    /**
     * @var string 业务类型。
     */
    private $type;

    /**
     * @var string 请求域名。
     */
    private $host = 'yunpian.com';

    /**
     * @var string API 的版本号，当前为 v2 。
     */
    private $version = 'v2';

    /**
     * @var string 资源名。
     */
    private $resource;

    /**
     * @var string 操作方法。
     */
    private $function;

    /**
     * @var string 请求响应的结果格式，默认 json 。
     */
    private $format = 'json';

    /**
     * @var array 响应结果。
     */
    private $result;

    /**
     * 初始化云片短信模块。
     * YunpianSms constructor.
     *
     * @param null|string $apikey   用户唯一标识。
     * @param null|string $protocol 请求协议。
     * @param null|string $format   请求响应的结果格式。
     */
    public function __construct($apikey = null, $protocol = null, $format = null)
    {
        if ($apikey) {
            $this->apikey = $apikey;
        } // else $this->apikey = '';

        if ($protocol) {
            $this->protocol = $protocol;
        }

        if ($format) {
            $this->format = $format;
        }
    }

    /**
     * 设置请求参数。
     *
     * @param string $type     业务类型。
     * @param string $resource 资源名称。
     * @param string $function 操作方法名。
     *
     * @return $this
     */
    private function path($type, $resource, $function)
    {
        $this->type = $type;
        $this->resource = $resource;
        $this->function = $function;

        return $this;
    }

    /**
     * 发送请求。
     *
     * @param mixed $data 请求参数。
     *
     * @return mixed
     * @throws Exception
     */
    private function post($data)
    {
        $ch = curl_init();

        $protocol = $this->protocol;
        $host = $this->host;
        $version = $this->version;
        $resource = $this->resource;
        $function = $this->function;
        $format = $this->format;

        $data['apikey'] = $this->apikey;

        $url = "$protocol://$host/$version/$resource/$function.$format";


        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept:text/plain;charset=utf-8',
            'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'
        ]);


        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($errno > 0) {
            throw new \Exception($error, $errno);
        }

        $res = json_decode($res, true);

        $http_status_code = isset($res['http_status_code']) ? $res['http_status_code'] : 200;
        $code = isset($res['code']) ? $res['code'] : 0;

        if (200 != $status || 200 != $http_status_code || 0 != $code) {
            $msg = isset($res['msg']) ? $res['msg'] : '';
            $detail = isset($res['detail']) ? $res['detail'] : '';

            throw new \Exception('ERR[' . $code . ']: ' . $msg . ' - ' . $detail);
        }

        return $res;
    }

    /**
     * 单条发送短信。
     *
     * @param string $mobile 目标手机号码。
     * @param string $text   短信内容。
     *
     * @return mixed
     * @throws Exception
     */
    public function singleSend($mobile, $text)
    {
        return $this->path('sms', 'sms', 'single_send')->post([
            'mobile' => $mobile,
            'text' => $text
        ]);
    }

    /**
     * 批量发送相同内容的短信给多个号码。
     *
     * @param string|array $mobiles 手机号，支持传入数组，字符串以英文逗号分隔。
     * @param string $text          短信内容。
     *
     * @return mixed
     * @throws Exception
     */
    public function batchSend($mobiles, $text)
    {
        if (is_array($mobiles)) {
            $mobiles = join(',', $mobiles);
        }

        return $this->path('sms', 'sms', 'batch_send')->post([
            'mobile' => $mobiles,
            'text' => $text
        ]);
    }

    /**
     * 指定短信模板 id 单发短信。
     *
     * @param string $mobile   目标手机号码。
     * @param integer $tpl_id  短信模板 id ，必须为整型，其他类型将抛出类型错误的异常。
     * @param array $tpl_value 变量名和变量值对，必须为数组类型，其他类型将抛出类型错误的异常。
     *
     * @return mixed
     * @throws Exception
     */
    public function tplSingleSend($mobile, $tpl_id, $tpl_value)
    {
        if (!is_int($tpl_id)) {
            throw new \Exception('$tpl_id must be integer!');
        }

        if (!is_array($tpl_value)) {
            throw new \Exception('$tpl_value must be array!');
        }

        $tpl_value_tmp = [];

        foreach ($tpl_value as $k => $v) {
            $tpl_value_tmp[] = urlencode('#' . $k . '#') . '=' . urlencode($v);
        }

        return $this->path('sms', 'sms', 'tpl_single_send')->post([
            'mobile' => $mobile,
            'tpl_id' => $tpl_id,
            'tpl_value' => join('&', $tpl_value_tmp),
        ]);
    }
}

