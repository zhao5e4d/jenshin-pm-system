<?php
declare(strict_types=1);
namespace zin;

global $lang, $app;

$objectType = data('objectType');
$objectID   = data('objectID');
$pageTitle  = data('title');
$isTask     = $objectType === 'task';

if($isTask)
{
    $app->loadLang('task');
    $pageTitle = $lang->task->addTaskRecord;
}

$actions   = array();
$actions[] = 'submit';
$actions[] = array('data-dismiss' => 'modal', 'text' => $lang->close);

set::title($pageTitle);

form
(
    set::url('action', 'comment', "objectType=$objectType&objectID=$objectID"),
    setClass('comment-form'),
    $isTask ? set::submitBtnText($lang->save) : null,
    $isTask ? div(setClass('text-gray mb-2 text-sm'), $lang->task->recordHint) : null,
    editor
    (
        set::name('actioncomment')
    ),
    !in_array($objectType, array('story', 'doctemplate')) ? fileSelector() : null,
    set::actions($actions)
);
