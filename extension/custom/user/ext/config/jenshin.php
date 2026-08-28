<?php
/**
 * 个人档案联系信息只保留手机、电话；通讯地址和邮编仍在表单后半段单独渲染。
 * 同时从表单配置中移除隐藏字段，避免保存时被写成空值。
 */
$config->user->contactField = 'mobile,phone';

$jxHiddenContacts = array('qq', 'dingding', 'weixin', 'skype', 'whatsapp', 'slack');
foreach($jxHiddenContacts as $field)
{
    unset($config->user->form->edit[$field]);
    unset($config->user->form->create[$field]);
    unset($config->user->form->batchCreate[$field]);
    unset($config->user->form->batchEdit[$field]);
}
