<?php
/**
 * 任务创建 / 编辑 / 批量编辑：去掉相关需求。
 * 批量创建：去掉所属模块和相关需求。
 * 从表单配置移除，保存时不会用默认值 0 覆盖库里已有关联。
 */
unset(
    $config->task->form->create['story'],
    $config->task->form->edit['story'],
    $config->task->form->batchedit['story'],
    $config->task->form->batchcreate['module'],
    $config->task->form->batchcreate['story']
);

if(isset($config->task->dtable->fieldList['story'])) unset($config->task->dtable->fieldList['story']);
if(isset($config->task->dtable->fieldList['fromBug'])) unset($config->task->dtable->fieldList['fromBug']);

$config->task->list->customBatchCreateFields = 'assignedTo,estimate,estStarted,deadline,desc,pri';
$config->task->custom->batchCreateFields     = 'assignedTo,estimate,estStarted,deadline,desc,pri';

