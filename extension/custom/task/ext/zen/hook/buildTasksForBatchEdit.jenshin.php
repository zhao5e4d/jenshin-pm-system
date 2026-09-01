<?php
/**
 * 批量编辑不再提交相关需求，补回原任务的 story，避免多人任务误改 storyVersion。
 */
foreach($taskData as $jxTaskID => $jxTask)
{
    if(isset($jxTask->story) || empty($oldTasks[$jxTaskID])) continue;
    $jxTask->story = $oldTasks[$jxTaskID]->story;
}
