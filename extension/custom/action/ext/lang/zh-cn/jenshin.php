<?php
/**
 * 任务的备注弹窗：标题与按钮改为任务记录。
 * 动态对象名：原「执行」改为「阶段」，与项目内阶段概念一致。
 */
global $app;

$lang->action->objectTypes['execution'] = $lang->executionCommon;
$lang->action->label->execution         = "{$lang->executionCommon}|execution|task|executionID=%s";

/* 任务协同：对象类型、详情链接和动态文案。 */
$lang->action->objectTypes['jxcollab'] = '任务协同';
$lang->action->label->jxcollab         = '任务协同|jxcollab|view|collabID=%s';

/* label 给动态列表的短文案，desc 给对象历史的整句。 */
$lang->action->label->jxcollabopened   = '发起协同';
$lang->action->label->jxcollabfeedback = '提交协同反馈';
$lang->action->label->jxcollabclosed   = '协同自动关闭';
$lang->action->label->jxcollabcanceled = '取消协同';

$lang->action->desc->jxcollabopened   = '$date, 由 <strong>$actor</strong> 发起协同 <strong>$extra</strong>。' . "\n";
$lang->action->desc->jxcollabfeedback = '$date, 由 <strong>$actor</strong> 提交协同反馈：<strong>$extra</strong>。' . "\n";
$lang->action->desc->jxcollabclosed   = '$date, 协同 <strong>$extra</strong> 的协同人已全部反馈，自动关闭。' . "\n";
$lang->action->desc->jxcollabcanceled = '$date, 由 <strong>$actor</strong> 取消协同 <strong>$extra</strong>。' . "\n";
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
