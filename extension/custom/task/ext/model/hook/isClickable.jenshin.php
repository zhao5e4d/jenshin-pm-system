<?php
/**
 * 父任务默认只能编辑 / 建子任务 / 指派。任务协同对父任务和子任务都开放，
 * 所以 collab 在这里提前放行，不走进后面的父任务限制。
 */
$jxAction = strtolower((string)$action);
if($jxAction === 'collab')
{
    if(!empty($task->deleted)) return false;
    if(!common::canModify('task', $task)) return false;
    if(isset($task->status) && in_array($task->status, array('closed', 'cancel'), true)) return false;
    return true;
}
