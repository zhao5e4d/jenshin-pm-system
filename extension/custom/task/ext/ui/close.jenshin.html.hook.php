<?php
/**
 * 关闭任务时，若任务下还有进行中的协同单，在弹窗顶部提示一下，不拦截关闭。
 */
namespace zin;

global $lang, $app;

$app->loadLang('jxcollab');

$jxTask    = data('task');
$jxTaskID  = !empty($jxTask->id) ? (int)$jxTask->id : (int)data('taskID');
$jxPending = $jxTaskID ? $app->control->loadModel('jxcollab')->getUnfinishedByTask($jxTaskID) : array();

if(!empty($jxPending))
{
    $jxItems = array();
    foreach($jxPending as $collab)
    {
        $jxItems[] = li
        (
            span(setClass('label gray mr-1'), zget($lang->jxcollab->typeList, $collab->type, $collab->type)),
            a
            (
                set::href(createLink('jxcollab', 'view', "collabID={$collab->id}")),
                set('data-toggle', 'modal'),
                set('data-size', 'lg'),
                $collab->title
            )
        );
    }

    onBeforeBuildNode(function($node) use ($lang, $jxItems)
    {
        static $done = false;
        if($done || !($node instanceof formPanel)) return;
        $done = true;

        $node->add(div
        (
            setClass('jx-collab-closing-tip alert warning mb-3'),
            div(setClass('font-bold'), $lang->jxcollab->taskClosing),
            ul(setClass('pl-4'), $jxItems),
            div(setClass('text-gray'), $lang->jxcollab->taskClosingTip)
        ), 'children', true);
    });
}
