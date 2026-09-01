<?php
/**
 * 任务编辑 / 批量编辑：去掉相关需求。
 * 从表单配置移除，保存时不会用默认值 0 覆盖库里已有关联。
 */
unset(
    $config->task->form->edit['story'],
    $config->task->form->batchedit['story']
);
