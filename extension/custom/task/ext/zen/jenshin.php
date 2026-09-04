<?php
/**
 * 编辑任务不再提交相关需求，保存后补回原关联，避免误改 story / storyVersion。
 * 批量创建不提供所属模块、相关需求，多项录入里也不列出。
 */
protected function getCustomFields(object $execution, string $action): array
{
    list($customFields, $checkedFields) = parent::getCustomFields($execution, $action);
    if($action !== 'batchCreate') return array($customFields, $checkedFields);

    unset($customFields['module'], $customFields['story'], $customFields['preview'], $customFields['copyStory']);
    $checkedFields = trim(str_replace(array(',module,', ',story,', ',preview,', ',copyStory,'), ',', ",{$checkedFields},"), ',');
    return array($customFields, $checkedFields);
}

protected function buildTaskForEdit(object $task): object|false
{
    $jxOldTask = $this->task->getByID($task->id);
    $task      = parent::buildTaskForEdit($task);
    if($task === false) return false;
    if(!isset($task->story) && $jxOldTask) $task->story = $jxOldTask->story;
    return $task;
}
