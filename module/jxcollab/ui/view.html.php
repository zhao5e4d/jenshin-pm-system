<?php
declare(strict_types=1);
/**
 * 协同单详情：协同人表态一览。
 */
namespace zin;

$statusClass = array('doing' => 'warning', 'closed' => 'success', 'canceled' => 'gray');
$resultClass = array('pass' => 'success', 'done' => 'success', 'fail' => 'danger');

$settled = 0;
foreach($collab->members as $rowMember) if($rowMember->settled) $settled ++;

$rows = array();
foreach($collab->members as $rowMember)
{
    $extraText = array();
    foreach($typeConfig['fields'] as $field => $fieldConfig)
    {
        $value = isset($rowMember->extra[$field]) ? trim((string)$rowMember->extra[$field]) : '';
        if($value === '') continue;
        $extraText[] = zget($lang->jxcollab->fieldList, $field, $field) . '：' . $value;
    }

    $rows[] = h::tr
    (
        h::td(zget($users, $rowMember->account, $rowMember->account)),
        h::td($rowMember->attitude ? span(setClass('label ' . ($rowMember->settled ? 'primary-pale' : 'warning-pale')), zget($lang->jxcollab->attitudeList, $rowMember->attitude, $rowMember->attitude)) : span(setClass('text-gray'), $lang->jxcollab->waiting)),
        h::td((string)$rowMember->content . ($extraText ? ' ' . implode('；', $extraText) : '')),
        h::td(helper::isZeroDate((string)$rowMember->feedbackDate) ? '' : substr((string)$rowMember->feedbackDate, 0, 16))
    );
}

$actions = array();
if($collab->status == 'doing' && !empty($canManage))
{
    $actions[] = btn
    (
        set::type('secondary'),
        set::url(createLink('jxcollab', 'edit', "collabID={$collab->id}")),
        set('data-toggle', 'modal'),
        $lang->edit
    );
    $actions[] = btn
    (
        set::type('danger'),
        setClass('ajax-submit'),
        set::url(createLink('jxcollab', 'cancel', "collabID={$collab->id}")),
        set('data-confirm', $lang->jxcollab->confirmCancel),
        $lang->jxcollab->cancel
    );
}

panel
(
    setID('jxCollabDetail'),
    set('data-jx-collab-id', (int)$collab->id),
    set('data-jx-read-url', createLink('jxcollab', 'view', "collabID={$collab->id}")),
    set('data-jx-collab-unread', !empty($isMember) && !empty($member) && (empty($member->readDate) || helper::isZeroDate((string)$member->readDate)) ? '1' : '0'),
    set::title($collab->title),
    to::titleSuffix
    (
        span(setClass('label gray ml-2'), zget($lang->jxcollab->typeList, $collab->type, $collab->type)),
        span(setClass('label ml-2 ' . zget($statusClass, $collab->status, '')), zget($lang->jxcollab->statusList, $collab->status, $collab->status)),
        $collab->result ? span(setClass('label ml-2 ' . zget($resultClass, $collab->result, '')), zget($lang->jxcollab->resultList, $collab->result, $collab->result)) : null,
        span(setClass('ml-2 text-gray'), sprintf($lang->jxcollab->listSummary, $settled, count($collab->members)))
    ),
    tableData
    (
        item(set::name($lang->jxcollab->task), $collab->taskName),
        item(set::name($lang->jxcollab->openedBy), zget($users, $collab->openedBy, $collab->openedBy) . ' ' . substr((string)$collab->openedDate, 0, 16)),
        item(set::name($lang->jxcollab->deadline), helper::isZeroDate((string)$collab->deadline) ? '-' : $collab->deadline),
        item(set::name($lang->jxcollab->desc), (string)$collab->desc === '' ? '-' : $collab->desc)
    ),
    h::table
    (
        setClass('table bordered mt-3'),
        h::thead(h::tr
        (
            h::th(set::width('140px'), $lang->jxcollab->member),
            h::th(set::width('120px'), $lang->jxcollab->attitude),
            h::th(zget($lang->jxcollab->contentLabel, $collab->type, $lang->jxcollab->content)),
            h::th(set::width('140px'), $lang->jxcollab->feedbackDate)
        )),
        h::tbody($rows)
    ),
    $actions ? div(setClass('mt-3 flex gap-2'), $actions) : null
);

if($collab->status == 'doing' && !empty($isMember) && !empty($member))
{
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
        formRow(div(setClass('text-gray pl-2'), $lang->jxcollab->feedbackTip))
    );
}

render();
