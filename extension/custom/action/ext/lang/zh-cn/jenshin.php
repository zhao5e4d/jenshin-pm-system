<?php
/**
 * 任务的备注弹窗：标题与按钮改为任务记录。
 * 动态对象名：原「执行」改为「阶段」，与项目内阶段概念一致。
 */
global $app;

$lang->action->objectTypes['execution'] = $lang->executionCommon;
$lang->action->label->execution         = "{$lang->executionCommon}|execution|task|executionID=%s";
if(isset($lang->action->search->objectTypeList['execution']))
{
    $lang->action->search->objectTypeList['execution'] = $lang->executionCommon;
}

$uri = $_SERVER['REQUEST_URI'] ?? '';
$isTaskComment = $app->rawModule == 'action'
    && $app->rawMethod == 'comment'
    && (strpos($uri, 'comment-task-') !== false || strpos($uri, 'objectType=task') !== false);

if($isTaskComment)
{
    if(!isset($lang->task))
    {
        $app->loadLang('task');
    }
    $lang->action->create  = $lang->task->addTaskRecord;
    $lang->action->comment = $lang->save;
}
