<?php
declare(strict_types=1);
/**
 * 发起 / 编辑协同单。edit 方法复用本视图，类型创建后不可改。
 */
namespace zin;

$collab  = $collab ?? null;
$isEdit  = !empty($collab);
$formUrl = $isEdit
    ? createLink('jxcollab', 'edit',   "collabID={$collab->id}")
    : createLink('jxcollab', 'create', "taskID={$task->id}");

$memberValue = array();
if($isEdit) foreach($collab->members as $member) $memberValue[] = $member->account;

$typeItems = array();
foreach($config->jxcollab->typeList as $type) $typeItems[$type] = zget($lang->jxcollab->typeList, $type, $type);

$currentType = $isEdit ? $collab->type : (string)key($typeItems);

jsVar('jxCollabTypeDesc', $lang->jxcollab->typeDesc);

formPanel
(
    set::title($isEdit ? $lang->jxcollab->edit : $lang->jxcollab->create),
    set::url($formUrl),
    formRow
    (
        formGroup
        (
            set::width('1/2'),
            set::label($lang->jxcollab->type),
            set::control('picker'),
            set::name('type'),
            set::items($typeItems),
            set::value($currentType),
            set::required(true),
            set::disabled($isEdit),
            on::change('[name="type"]')->do('$("#jxCollabTypeTip").text((window.jxCollabTypeDesc || {})[$(this).val()] || "")')
        ),
        formGroup
        (
            set::width('1/2'),
            set::label($lang->jxcollab->deadline),
            set::control('date'),
            set::name('deadline'),
            set::value($isEdit && !helper::isZeroDate((string)$collab->deadline) ? $collab->deadline : '')
        )
    ),
    $isEdit ? input(set::type('hidden'), set::name('type'), set::value($collab->type)) : null,
    formRow(formGroup(set::label($lang->jxcollab->task), div(setClass('pt-1'), $task ? $task->name : ''))),
    formRow(formGroup(set::label($lang->jxcollab->title), set::name('title'), set::value($isEdit ? $collab->title : ''), set::required(true))),
    formRow
    (
        formGroup
        (
            set::label($lang->jxcollab->members),
            set::required(true),
            picker(set::name('members[]'), set::items($users), set::multiple(true), set::value(implode(',', $memberValue)))
        )
    ),
    formRow(formGroup(set::label($lang->jxcollab->desc), textarea(set::name('desc'), set::rows(3), set::value($isEdit ? (string)$collab->desc : '')))),
    formRow(formGroup
    (
        set::label($lang->jxcollab->files),
        fileSelector($isEdit && !empty($collab->files) ? set::defaultFiles(array_values($collab->files)) : null)
    )),
    formRow(div(setClass('text-gray pl-2'), span(setID('jxCollabTypeTip'), zget($lang->jxcollab->typeDesc, $currentType, ''))))
);

render();
