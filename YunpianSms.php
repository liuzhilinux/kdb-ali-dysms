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
     * @var string 短信验证码模板 id 。
     */
    private $verifyPhoneTplId;

    /**
     * @var string 短信验证码参数字段。
     */
    private $verifyPhoneCodeField;

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

        // $this->verifyPhoneTplId = '';
        // $this->verifyPhoneCodeField = '';
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
     * @param string $mobile 接收的手机号，仅支持单号码发送，不需要带+86 前缀。
     * @param string $text   需要发送的短信内容，需要与已审核的短信模板相匹配。
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
     * @param string|array $mobiles 接收的手机号，支持传入数组，字符串以英文逗号分隔。建议单次提交 200 个手机号以内，不要超过 1000 个，不需要带+86 前缀。
     * @param string $text          需要发送的短信内容，需要与已审核的短信模板相匹配。
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
     * @param string $mobile   接收的手机号，不需要带+86 前缀    。
     * @param int $tpl_id      短信模板 id ，必须为整型，其他类型将抛出类型错误的异常。
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
     * @param int $tpl_id           短信模板 id ，必须为整型，其他类型将抛出类型错误的异常。
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
     * @param int $page_size 每页个数，最大 100 个，默认 20 个。
     * @param int $page_num  页码，默认第 1 页。
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
     * @param int $page_size 每页个数，最大 100 个，默认 20 个。
     * @param int $page_num  页码，默认第 1 页。
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
     * @param int $notify_type          审核结果短信通知的方式:
     *
     *                                      0 表示需要通知,默认;
     *                                      1 表示仅审核不通过时通知;
     *                                      2 表示仅审核通过时通知;
     *                                      3 表示不需要通知。
     *
     * @param string $website           验证码类模板对应的官网注册页面，验证码类模板必填。
     * @param int $tpl_type             1 代表验证码类模板，验证码类模板必填。
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
     * @param int $tpl_id 模板 id 。
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
     * @param int $tpl_id               模板id。
     * @param string $tpl_content       模板内容，必须以带符号【】的签名开头。
     * @param string $website           验证码类模板对应的官网注册页面，验证码类模板必填。
     * @param int $tpl_type             1 代表验证码类模板，验证码类模板必填。
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
     * @param int $tpl_id 模板id。
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

    /**
     * 添加签名。
     *
     * @param string $sign           签名内容，不能包含【】，3-8 个字，不能是纯英文和数字。
     * @param bool $notify           是否短信通知结果，默认 true 。
     * @param bool $apply_vip        是否申请专用通道，默认 false 。
     * @param string $industry_type  所属行业，默认“其它”，可选项(必须完全一致，枚举值如下：
     *
     *                                  1. 游戏;
     *                                  2. 移动应用;
     *                                  3. 视频;
     *                                  4. IT/通信/电子服务;
     *                                  5. 电子商务;
     *                                  6. 金融;
     *                                  7. 网站;
     *                                  8. 商业服务;
     *                                  9. 房地产/建筑;
     *                                  10. 零售/租赁/贸易;
     *                                  11. 生产/加工/制造;
     *                                  12. 交通/物流;
     *                                  13. 文化传媒;
     *                                  14. 能源/电气;
     *                                  15. 政府企业;
     *                                  16. 农业;
     *                                  17. 物联网;
     *                                  18. 其它。
     *
     * @param string $license_url    签名对应的营业执照或其他企业资质的图片文件 URL 。
     * @param string $license_base64 签名对应的资质图片进行 base64 编码格式转换后的字符串。
     *
     * @return mixed
     * @throws Exception
     */
    public function addSign($sign, $notify = true, $apply_vip = false, $industry_type = '', $license_url = '', $license_base64 = '')
    {
        $data = ['sign' => $sign];

        foreach (['notify', 'apply_vip'] as $k) {
            if ($$k) {
                $data[$k] = true;
            }
        }

        foreach (['industry_type', 'license_url', 'license_base64'] as $k) {
            if (!empty($$k)) {
                $data[$k] = $$k;
            }
        }

        return $this->path('sms', 'sign', 'add')->post($data);
    }

    /**
     * 获取签名。
     *
     * @param string $sign   返回所有包含该内容的签名（模糊匹配），若要获取指定签名可加上符号，如【云片网】。
     * @param int $page_num  页码，1 开始，不带或者格式错误返回全部。
     * @param int $page_size 返回条数，必须大于 0 ，不带或者格式错误返回全部。
     *
     * @return mixed
     * @throws Exception
     */
    public function getSign($sign = '', $page_num = 0, $page_size = 0)
    {
        if (!is_int($page_num)) {
            throw new \Exception('$page_num must be integer!');
        }

        if (!is_int($page_size)) {
            throw new \Exception('$page_size must be integer!');
        }

        $data = [];

        if (!empty($sign)) {
            $data['sign'] = $sign;
        }

        if ($page_num > 0) {
            $data['page_num'] = $page_num;
        }

        if ($page_size > 0) {
            $data['page_size'] = $page_size;
        }

        return $this->path('sms', 'sign', 'get')->post($data);
    }

    /**
     * 修改签名。
     *
     * @param string $old_sign       完整签名内容，用于指定修改哪个签名，可以加【】也可不加。
     * @param string $sign           修改后签名内容，不带【】，无此参数表示不修改。
     * @param bool $notify           是否短信通知结果，默认 true 。
     * @param bool $apply_vip        是否申请专用通道，默认 false 。
     * @param string $industry_type  所属行业，默认“其它”，可选项(必须完全一致
     * @param string $license_url    签名对应的营业执照或其他企业资质的图片文件 URL，
     * @param string $license_base64 签名对应的资质图片进行 base64 编码格式转换后的字符串。
     *
     * @return mixed
     * @throws Exception
     */
    public function updateSign($old_sign, $sign = '', $notify = true, $apply_vip = false, $industry_type = '', $license_url = '', $license_base64 = '')
    {
        $data = ['old_sign' => $old_sign];

        foreach (['notify', 'apply_vip'] as $k) {
            if ($$k) {
                $data[$k] = true;
            }
        }

        foreach (['sign', 'industry_type', 'license_url', 'license_base64'] as $k) {
            if (!empty($$k)) {
                $data[$k] = $$k;
            }
        }

        return $this->path('sms', 'sign', 'update')->post($data);
    }

    /**
     * 查短信发送记录。
     *
     * @param int|string $start_time 短信发送开始时间，支持任意格式。
     * @param int|string $end_time   短信发送结束时间，支持任意格式。
     * @param int $page_num          页码，默认值为 1 。
     * @param int $page_size         每页个数，最大 100 个，默认值为 20 。
     * @param string $mobile         需要查询的手机号。
     *
     * @return mixed
     * @throws Exception
     */
    public function getRecord($start_time, $end_time, $page_num = 1, $page_size = 20, $mobile = '')
    {
        if (!is_int($page_num)) {
            throw new \Exception('$page_num must be integer!');
        }

        if (!is_int($page_size)) {
            throw new \Exception('$page_size must be integer!');
        }

        $start_time = is_int($start_time) ? date('Y-m-d H:i:s', $start_time) : date('Y-m-d H:i:s', strtotime($start_time));
        $end_time = is_int($end_time) ? date('Y-m-d H:i:s', $end_time) : date('Y-m-d H:i:s', strtotime($end_time));

        $data = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page_num' => $page_num,
            'page_size' => $page_size
        ];

        if (!empty($mobile)) {
            $data['mobile'] = $mobile;
        }

        return $this->path('sms', 'sms', 'get_record')->post($data);
    }

    /**
     * 查账户信息。
     *
     * @return mixed
     * @throws Exception
     */
    public function getUserInfo()
    {
        return $this->path('sms', 'user', 'get')->post();
    }

    /**
     * 修改账号信息。
     *
     * @param null|string $emergency_contact 紧急联系人。
     * @param null|string $emergency_mobile  紧急联系人手机号。
     * @param null|string $alarm_balance     短信余额提醒阈值。 一天只提示一次。
     *
     * @return mixed
     * @throws Exception
     */
    public function setUserInfo($emergency_contact = null, $emergency_mobile = null, $alarm_balance = null)
    {
        $data = [];

        foreach (['emergency_contact', 'emergency_mobile', 'alarm_balance'] as $k) {
            if (!empty($$k)) {
                $data[$k] = $$k;
            }
        }

        if (count($data) <= 0) {
            throw new \Exception('At least one parameter required is not empty!');
        }

        return $this->path('sms', 'user', 'set')->post($data);
    }

    /**
     * 发送短信 - 通用方法。
     *
     * @param string|array $mobiles 手机号码，多个号码以英文逗号分隔，或打包成数组。
     * @param int|string $contents  短信内容或模板 id 。
     * @param array $params         变量名和变量值对，必须为数组类型，其他类型将抛出类型错误的异常。
     *
     * @return mixed
     * @throws Exception
     */
    public function send($mobiles, $contents, $params = [])
    {
        if (!is_array($params)) {
            throw new \Exception('$params must be array!');
        }

        if (is_array($mobiles)) {
            $mobiles = join(',', $mobiles);
        }

        $is_batch_send = strpos($mobiles, ',') !== false;

        if (is_numeric($contents)) {
            $contents = intval($contents);

            return $is_batch_send ?
                $this->tplBatchSend($mobiles, $contents, $params) :
                $this->tplSingleSend($mobiles, $contents, $params);

        } else {
            return $is_batch_send ?
                $this->singleSend($mobiles, $contents) :
                $this->batchSend($mobiles, $contents);
        }
    }

    /**
     * 发送短信验证码。
     *
     * @param string $mobile    接收短信验证码的手机号。
     * @param int $digit        验证码位数。
     * @param null $verify_code 验证码。
     * @param null $tpl_id      短信模板 id 。
     * @param null $field       验证码参数字段。
     *
     * @return mixed
     * @throws Exception
     */
    public function sendVerifyCode($mobile, $digit = 6, $verify_code = null, $tpl_id = null, $field = null)
    {
        if (!empty($verify_code)) {
            $verify_code = '';

            for ($i = 0; $i < intval($digit); $i++) $verify_code .= mt_rand(0, 9);
        }

        if (empty($tpl_id)) $tpl_id = $this->verifyPhoneTplId;

        if (empty($field)) $field = $this->verifyPhoneCodeField;

        $res = $this->tplSingleSend($mobile, $tpl_id, [$field => $verify_code]);

        $res['mobile'] = $mobile;
        $res['verify_code'] = $verify_code;

        return $res;
    }
}

