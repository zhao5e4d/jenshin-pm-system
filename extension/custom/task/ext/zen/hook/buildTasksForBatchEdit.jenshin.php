<?php
/**
 * 批量编辑不再提交相关需求 / 所属模块，补回原值，避免误改 storyVersion 或把 module 写成 0。
 */
foreach($taskData as $jxTaskID => $jxTask)
{
    if(empty($oldTasks[$jxTaskID])) continue;
    if(!isset($jxTask->story))  $jxTask->story  = $oldTasks[$jxTaskID]->story;
    if(!isset($jxTask->module)) $jxTask->module = $oldTasks[$jxTaskID]->module;
}
