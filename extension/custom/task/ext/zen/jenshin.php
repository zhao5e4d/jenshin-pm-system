<?php
/**
 * 编辑任务不再提交相关需求，保存后补回原关联，避免误改 story / storyVersion。
 */
protected function buildTaskForEdit(object $task): object|false
{
    $jxOldTask = $this->task->getByID($task->id);
    $task      = parent::buildTaskForEdit($task);
    if($task === false) return false;
    if(!isset($task->story) && $jxOldTask) $task->story = $jxOldTask->story;
    return $task;
}
