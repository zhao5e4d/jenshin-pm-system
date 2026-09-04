<?php
/**
 * 添加/设置任务执行：去掉需求/测试/执行/发布负责人。
 * 从表单配置移除，编辑保存时不会用空值覆盖库里已有数据。
 */
unset(
    $config->execution->form->create['PO'],
    $config->execution->form->create['QD'],
    $config->execution->form->create['PM'],
    $config->execution->form->create['RD'],
    $config->execution->form->edit['PO'],
    $config->execution->form->edit['QD'],
    $config->execution->form->edit['PM'],
    $config->execution->form->edit['RD']
);

foreach(array('create', 'edit') as $jxFormMethod)
{
    if(empty($config->execution->{$jxFormMethod}->requiredFields)) continue;
    foreach(array('PO', 'QD', 'PM', 'RD') as $jxOwnerField)
    {
        $config->execution->{$jxFormMethod}->requiredFields = trim(str_replace(",{$jxOwnerField},", ',', ",{$config->execution->{$jxFormMethod}->requiredFields},"), ',');
    }
}
