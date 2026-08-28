<?php
/**
 * 编辑个人档案时不提交已隐藏的即时通讯字段，避免覆盖库里的旧值。
 */
$jxHiddenContacts = array('qq', 'dingding', 'weixin', 'skype', 'whatsapp', 'slack');
foreach($jxHiddenContacts as $field)
{
    unset($config->my->form->editProfile[$field]);
}
