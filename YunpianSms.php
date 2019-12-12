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
    private function post($data = [])
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

    /**
     * 指定短信模板 id 批量发送相同内容的短信给多个号码。
     *
     * @param string|array $mobiles 手机号，支持传入数组，字符串以英文逗号分隔。
     * @param integer $tpl_id       短信模板 id ，必须为整型，其他类型将抛出类型错误的异常。
     * @param array $tpl_value      变量名和变量值对，必须为数组类型，其他类型将抛出类型错误的异常。
     *
     * @return mixed
     * @throws Exception
     */
    public function tplBatchSend($mobiles, $tpl_id, $tpl_value)
    {
        if (is_array($mobiles)) {
            $mobiles = join(',', $mobiles);
        }

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

        return $this->path('sms', 'sms', 'tpl_batch_send')->post([
            'mobile' => $mobiles,
            'tpl_id' => $tpl_id,
            'tpl_value' => join('&', $tpl_value_tmp),
        ]);
    }

    /**
     * 获取状态报告。
     * 状态报告保存时间为72小时。
     * 不可指定页码，可以通过多次获取数据，当最终获取的数据为空则表示没有数据。
     * 注意，已成功获取的数据将会删除，请妥善处理接口返回的数据。
     *
     * @param integer $page_size 每页个数，最大 100 个，默认 20 个。
     * @param integer $page_num  页码，默认第 1 页。
     *
     * @return mixed
     * @throws Exception
     */
    public function pullStatus($page_size = 20, $page_num = 1)
    {
        if (!is_int($page_size)) {
            throw new \Exception('$page_size must be integer!');
        }

        if (!is_int($page_num)) {
            throw new \Exception('$page_num must be integer!');
        }

        return $this->path('sms', 'sms', 'pull_status')->post([
            'page_size' => $page_size,
            'page_num' => $page_num,
        ]);
    }

    /**
     * 获取回复短信。
     * 回复短信保存时间为72小时。
     * 不可指定页码，可以通过多次获取数据，当最终获取的数据为空则表示没有数据。
     * 注意，已成功获取的数据将会删除，请妥善处理接口返回的数据。
     *
     * @param integer $page_size 每页个数，最大 100 个，默认 20 个。
     * @param integer $page_num  页码，默认第 1 页。
     *
     * @return mixed
     * @throws Exception
     */
    public function pullReply($page_size = 20, $page_num = 1)
    {
        if (!is_int($page_size)) {
            throw new \Exception('$page_size must be integer!');
        }

        if (!is_int($page_num)) {
            throw new \Exception('$page_num must be integer!');
        }

        return $this->path('sms', 'sms', 'pull_reply')->post([
            'page_size' => $page_size,
            'page_num' => $page_num,
        ]);
    }

    /**
     * 添加模版。
     *
     * @param string $tpl_content       模板内容，必须以带符号【】的签名开头。
     * @param integer $notify_type      审核结果短信通知的方式:
     *
     *                                      0 表示需要通知,默认;
     *                                      1 表示仅审核不通过时通知;
     *                                      2 表示仅审核通过时通知;
     *                                      3 表示不需要通知。
     *
     * @param string $website           验证码类模板对应的官网注册页面，验证码类模板必填。
     * @param integer $tpl_type         1 代表验证码类模板，验证码类模板必填。
     * @param string $apply_description 说明模板的发送场景和对象。
     *
     * @return mixed
     * @throws Exception
     */
    public function addTpl($tpl_content, $notify_type = 0, $website = '', $tpl_type = 0, $apply_description = '')
    {
        if (!is_int($notify_type)) {
            throw new \Exception('$notify_type must be integer!');
        }

        if (!is_int($tpl_type)) {
            throw new \Exception('$tpl_type must be integer!');
        }

        $data = [
            'tpl_content' => $tpl_content,
            'notify_type' => $notify_type,
            'apply_description' => $apply_description,
        ];

        if (1 === $tpl_type && !empty($website)) {
            $data['website'] = $website;
            $data['tplType'] = $tpl_type;
        }

        return $this->path('sms', 'tpl', 'add')->post($data);
    }

    /**
     * 获取模板。
     *
     * @param integer $tpl_id 模板id。
     *
     * @return mixed
     * @throws Exception
     */
    public function getTpl($tpl_id)
    {
        if (!is_int($tpl_id)) {
            throw new \Exception('$tpl_id must be integer!');
        }

        return $this->path('sms', 'tpl', 'get')->post(['tpl_id' => $tpl_id]);
    }

    /**
     * 修改模版。
     *
     * @param integer $tpl_id           模板id。
     * @param string $tpl_content       模板内容，必须以带符号【】的签名开头。
     * @param string $website           验证码类模板对应的官网注册页面，验证码类模板必填。
     * @param integer $tpl_type         1 代表验证码类模板，验证码类模板必填。
     * @param string $apply_description 说明模板的发送场景和对象。
     *
     * @return mixed
     * @throws Exception
     */
    public function updateTpl($tpl_id, $tpl_content, $website = '', $tpl_type = 0, $apply_description = '')
    {
        if (!is_int($tpl_id)) {
            throw new \Exception('$tpl_id must be integer!');
        }

        if (!is_int($tpl_type)) {
            throw new \Exception('$tpl_type must be integer!');
        }

        $data = [
            'tpl_id' => $tpl_id,
            'tpl_content' => $tpl_content,
            'apply_description' => $apply_description,
        ];

        if (1 === $tpl_type && !empty($website)) {
            $data['website'] = $website;
            $data['tplType'] = $tpl_type;
        }

        return $this->path('sms', 'tpl', 'update')->post($data);
    }

    /**
     * 删除模板。
     *
     * @param integer $tpl_id 模板id。
     *
     * @return mixed
     * @throws Exception
     */
    public function delTpl($tpl_id)
    {
        if (!is_int($tpl_id)) {
            throw new \Exception('$tpl_id must be integer!');
        }

        return $this->path('sms', 'tpl', 'del')->post(['tpl_id' => $tpl_id]);
    }
}

