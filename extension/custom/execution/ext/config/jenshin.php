<?php
/**
 * 设置迭代：去掉产品/测试/迭代/发布负责人。
 * 从表单配置移除，保存时不会用空值覆盖库里已有数据。
 */
unset(
    $config->execution->form->edit['PO'],
    $config->execution->form->edit['QD'],
    $config->execution->form->edit['PM'],
    $config->execution->form->edit['RD']
);

if(!empty($config->execution->edit->requiredFields))
{
    foreach(array('PO', 'QD', 'PM', 'RD') as $jxOwnerField)
    {
        $config->execution->edit->requiredFields = trim(str_replace(",{$jxOwnerField},", ',', ",{$config->execution->edit->requiredFields},"), ',');
    }
}
