CREATE TABLE `wfx_sms_sign`
(
  `id`             int UNSIGNED        NOT NULL AUTO_INCREMENT,
  `sign_name`      char(12)            NOT NULL COMMENT '签名名称， 2 到 12 个字，一个中文字符或一个英文字符均算做一个字',
  `sign_source`    tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '签名来源，\r\n0：企事业单位的全称或简称；\r\n1：工信部备案网站的全称或简称；\r\n2：APP应用的全称或简称；\r\n3：公众号或小程序的全称或简称；\r\n4：电商平台店铺名的全称或简称；\r\n5：商标名的全称或简称',
  `remark`         varchar(255)        NOT NULL DEFAULT '' COMMENT '短信签名申请说明',
  `sign_file_list` text                NULL COMMENT '签名的证明文件，为 JSON 格式，图片为 Base64',
  `create_date`    datetime            NULL COMMENT '短信签名的创建日期和时间',
  `reason`         varchar(255)        NOT NULL DEFAULT '' COMMENT '审核备注',
  `sign_status`    tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '签名审核状态。其中：\r\n0：审核中；\r\n1：审核通过；\r\n2：审核失败',
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COMMENT = '短信签名';

CREATE TABLE `wfx_sms_template`
(
  `id`               int UNSIGNED        NOT NULL AUTO_INCREMENT,
  `template_name`    char(30)            NOT NULL COMMENT '模板名称',
  `template_code`    char(20)            NOT NULL DEFAULT '' COMMENT '短信模板 CODE',
  `template_type`    tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '短信类型。其中：\r\n0：验证码；\r\n1：短信通知；\r\n2：推广短信；\r\n3：国际/港澳台消息',
  `template_content` varchar(1000)       NOT NULL COMMENT '模板内容',
  `remark`           varchar(255)        NULL COMMENT '短信模板申请说明',
  `create_date`      datetime            NULL COMMENT '短信模板的创建日期和时间',
  `reason`           varchar(255)        NOT NULL DEFAULT '' COMMENT '审核备注',
  `template_status`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '模板审核状态。其中：\r\n0：审核中；\r\n1：审核通过；\r\n2：审核失败',
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COMMENT = '短信模板';

CREATE TABLE `wfx_sms_send_queue`
(
  `id`              int UNSIGNED        NOT NULL AUTO_INCREMENT,
  `phone_number`    char(18)            NOT NULL COMMENT '接收短信的手机号码',
  `sign_name`       char(12)            NOT NULL COMMENT '短信签名名称',
  `template_code`   char(20)            NOT NULL COMMENT '短信模板ID',
  `out_id`          varchar(255)        NOT NULL DEFAULT '' COMMENT '外部流水扩展字段',
  `template_param`  text                NULL COMMENT '短信模板变量对应的实际值， JSON 格式',
  `biz_id`          char(40)            NOT NULL DEFAULT '' COMMENT '发送回执ID',
  `activity_id`     int UNSIGNED        NOT NULL COMMENT '活动 id',
  `is_same`         tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为相同内容：0 否；1 是',
  `is_sent`         tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已发送：0 否；1 是',
  `r_code`          char(48)            NOT NULL DEFAULT '' COMMENT '请求状态码',
  `r_message`       varchar(255)        NOT NULL DEFAULT '' COMMENT '状态码的描述',
  `r_content`       varchar(1000)       NOT NULL DEFAULT '' COMMENT '短信内容',
  `r_err_code`      char(24)            NOT NULL DEFAULT '' COMMENT '运营商短信状态码，成功为 DELIVERED',
  `r_out_id`        varchar(255)        NOT NULL DEFAULT '' COMMENT '外部流水扩展字段',
  `r_phone_num`     char(18)            NOT NULL DEFAULT '' COMMENT '接收短信的手机号码',
  `r_receive_date`  datetime            NULL COMMENT '短信接收日期和时间',
  `r_send_date`     datetime            NULL COMMENT '短信发送日期和时间',
  `r_send_status`   tinyint(1) UNSIGNED NOT NULL COMMENT '短信发送状态，包括：\r\n1：等待回执；\r\n2：发送失败；\r\n3：发送成功',
  `r_template_code` varchar(20)         NOT NULL COMMENT '短信模板ID',
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COMMENT = '短信发送记录';
