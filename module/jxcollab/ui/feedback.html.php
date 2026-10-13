<?php
declare(strict_types=1);
/**
 * 协同人填写反馈。表态选项与额外字段由协同类型决定。
 */
namespace zin;

$attitudeItems = array();
foreach(array_keys($typeConfig['attitudes']) as $attitude)
{
    $attitudeItems[] = array('value' => $attitude, 'text' => zget($lang->jxcollab->attitudeList, $attitude, $attitude));
}

$contentLabel = zget($lang->jxcollab->contentLabel, $collab->type, $lang->jxcollab->content);
$extraGroups  = array();
foreach($typeConfig['fields'] as $field => $fieldConfig)
{
    $value = isset($member->extra[$field]) ? $member->extra[$field] : '';
    $extraGroups[] = formRow(formGroup
    (
        set::label(zget($lang->jxcollab->fieldList, $field, $field)),
        $fieldConfig['control'] == 'date'
            ? datePicker(set::name($field), set::value($value))
            : textarea(set::name($field), set::rows(3), set::value($value))
    ));
}

formPanel
(
    set::title($lang->jxcollab->feedback),
    set::submitBtnText($lang->jxcollab->saveFeedback),
    set::url(createLink('jxcollab', 'feedback', "collabID={$collab->id}")),
    formRow(formGroup
    (
        set::label($lang->jxcollab->title),
        div
        (
            setClass('pt-1'),
            span(setClass('label gray mr-2'), zget($lang->jxcollab->typeList, $collab->type, $collab->type)),
            $collab->title
        )
    )),
    empty($typeConfig['hideAttitude']) ? formRow(formGroup
    (
        set::label($lang->jxcollab->attitude),
        set::required(true),
        radioList(set::name('attitude'), set::items($attitudeItems), set::value((string)$member->attitude), set::inline(true))
    )) : null,
    formRow(formGroup
    (
        set::label($contentLabel),
        set::required($typeConfig['contentRequired'] === true),
        textarea(set::name('content'), set::rows(4), set::value((string)$member->content))
    )),
    $extraGroups,
    formRow(formGroup
    (
        set::label($lang->jxcollab->feedbackFiles),
        fileSelector(!empty($member->files) ? set::defaultFiles(array_values($member->files)) : null)
    )),
    formRow(div(setClass('text-gray pl-2'), $lang->jxcollab->feedbackTip))
);

render();
