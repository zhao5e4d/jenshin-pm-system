<?php
declare(strict_types=1);
namespace zin;

global $lang, $app;

$objectType = data('objectType');
$actionID   = data('actionID');
$comment    = data('comment');
$files      = data('files');
$pageTitle  = data('title');
$isTask     = $objectType === 'task';

if($isTask)
{
    $app->loadLang('task');
    $pageTitle = $lang->task->editTaskRecord;
}

$actions   = array();
$actions[] = 'submit';
$actions[] = isInModal() ? array('data-dismiss' => 'modal', 'text' => $lang->close) : 'cancel';

set::title($pageTitle);

form
(
    set::url('action', 'editComment', "actionID=$actionID"),
    setClass('comment-form is-edit'),
    $isTask ? set::submitBtnText($lang->save) : null,
    editor
    (
        set::name('lastComment'),
        html($comment)
    ),
    $objectType != 'story' ? fileSelector(set::defaultFiles(array_values($files))) : null,
    set::actions($actions)
);
