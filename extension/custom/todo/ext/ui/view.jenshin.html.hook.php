<?php
/**
 * 任务协同生成的待办：详情里加「打开协同单」，避免只能从说明里找链接。
 */
namespace zin;

$jxTodo = data('todo');
if(empty($jxTodo) || ($jxTodo->type ?? '') !== 'jxcollab' || empty($jxTodo->objectID)) return;

global $lang, $app;
$app->loadLang('jxcollab');

onBeforeBuildNode(function($node) use ($lang, $jxTodo)
{
    if(!($node instanceof detail) && !($node instanceof panel) && !($node instanceof formPanel)) return;

    static $injected = false;
    if($injected) return;
    $injected = true;

    $link = a
    (
        setClass('btn primary'),
        set::href(createLink('jxcollab', 'view', "collabID={$jxTodo->objectID}")),
        set('data-toggle', 'modal'),
        set('data-size', 'lg'),
        $lang->jxcollab->openCollab
    );

    if(method_exists($node, 'add')) $node->add(div(setClass('mt-3'), $link), 'children', true);
});
