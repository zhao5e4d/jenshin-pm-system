<?php
declare(strict_types=1);
namespace zin;

$healthLabel = array('green' => 'success', 'yellow' => 'warning', 'red' => 'danger');
$extraHealth = $extra?->health ?? 'green';

$stageBlocks = array();
foreach($stages as $stage)
{
    $checks = array();
    foreach($stage->checks as $check)
    {
        $checks[] = div
        (
            setClass('flex items-center gap-2 py-1'),
            a
            (
                setClass('btn size-sm ghost ajax-submit'),
                set::href(createLink('jxcore', 'togglecheck', "checkID={$check->id}&done=" . ($check->done ? 0 : 1))),
                set('data-confirm', false),
                $check->done ? icon('checked') : icon('circle')
            ),
            span(setClass($check->done ? 'line-through text-gray' : ''), ($check->required ? '* ' : '') . $check->name)
        );
    }

    $actions = array();
    if($stage->status == 'doing' || $stage->status == 'rejected')
    {
        $actions[] = btn(setClass('primary size-sm ajax-submit'), set::url(createLink('jxcore', 'submitstage', "stageID={$stage->id}")), $lang->jxcore->submit);
    }
    if($stage->status == 'submitted')
    {
        $actions[] = btn(setClass('success size-sm ajax-submit'), set::url(createLink('jxcore', 'approvestage', "stageID={$stage->id}")), $lang->jxcore->approve);
        $actions[] = form
        (
            set::actions(array()),
            set::url(createLink('jxcore', 'approvestage', "stageID={$stage->id}")),
            setClass('inline-flex gap-2 items-center'),
            input(set::type('hidden'), set::name('result'), set::value('reject')),
            input(set::name('comment'), set::placeholder($lang->jxcore->comment), setClass('w-40')),
            btn(setClass('danger size-sm'), set::btnType('submit'), $lang->jxcore->reject)
        );
    }

    $stageBlocks[] = div
    (
        setClass('border rounded-md p-3 mb-3 bg-white'),
        div
        (
            setClass('flex justify-between items-center mb-2'),
            div
            (
                setClass('font-bold'),
                $stage->order . '. ' . $stage->name,
                span(setClass('label circle ml-2'), zget($lang->jxcore->statusList, $stage->status, $stage->status))
            ),
            span(setClass('text-gray'), formatTime($stage->begin, DT_DATE1) . ' ~ ' . formatTime($stage->end, DT_DATE1))
        ),
        $checks,
        div(setClass('mt-2 flex gap-2'), $actions)
    );
}

$costRows = array();
foreach($costs as $cost)
{
    $costRows[] = h::tr
    (
        h::td($cost->occurDate),
        h::td($cost->dept),
        h::td($cost->category),
        h::td($cost->amount),
        h::td($cost->desc)
    );
}

detailHeader
(
    to::prefix
    (
        backBtn($lang->goback, setClass('secondary'), set::icon('back')),
        entityLabel(set::entityID($row->id), set::text($row->name), set::level(1)),
        span(setClass('label ' . zget($healthLabel, $extraHealth, '')), zget($lang->jxcore->healthList, $extraHealth, $extraHealth))
    ),
    to::suffix
    (
        $row->project ? btn(set::url(createLink('project', 'view', "projectID={$row->project}")), $lang->jxcore->openProject) : null,
        $row->project ? btn(set::url(createLink('doc', 'projectSpace', "objectID={$row->project}")), $lang->jxcore->openDoc) : null,
        hasPriv('jxregistration', 'edit') ? btn(set::icon('edit'), set::url(createLink('jxregistration', 'edit', "id={$row->id}")), $lang->edit) : null
    )
);

detailBody
(
    sectionList
    (
        section
        (
            set::title($lang->jxregistration->common),
            tableData
            (
                item(set::name($lang->jxregistration->product), $product ? $product->name : '-'),
                item(set::name($lang->jxregistration->type), zget($lang->jxregistration->typeList, $row->type)),
                item(set::name($lang->jxregistration->code), $row->code),
                item(set::name($lang->jxregistration->category), $row->category),
                item(set::name($lang->jxregistration->path), $row->path),
                item(set::name($lang->jxregistration->acceptNo), $row->acceptNo),
                item(set::name($lang->jxregistration->certNo), $row->certNo),
                item(set::name($lang->jxregistration->certValidTo), $row->certValidTo),
                item(set::name($lang->jxregistration->owner), $row->owner),
                item(set::name($lang->jxregistration->leadDept), $row->leadDept),
                item(set::name($lang->jxcore->progress), ($extra?->progress ?? 0) . '%'),
                item(set::name($lang->jxcore->blocker), $extra?->blocker ?? '-')
            )
        ),
        section(set::title($lang->jxregistration->stages), $stageBlocks),
        section
        (
            set::title($lang->jxregistration->costs),
            div
            (
                setClass('mb-2 flex gap-4 items-center'),
                span($lang->jxcore->budget . '：' . ($summary->budget ?? $row->budget)),
                span($lang->jxcore->actual . '：' . ($summary->actual ?? 0)),
                span($lang->jxcore->delta . '：' . ($summary->delta ?? 0)),
                $row->project ? btn(setClass('primary size-sm'), set::url(createLink('jxcore', 'addcost', "projectID={$row->project}")), set('data-toggle', 'modal'), $lang->jxcore->addcost) : null
            ),
            h::table
            (
                setClass('table bordered'),
                h::thead(h::tr(h::th($lang->jxcore->occurDate), h::th($lang->jxcore->dept), h::th($lang->jxcore->category), h::th($lang->jxcore->amount), h::th($lang->jxcore->desc))),
                h::tbody($costRows ?: h::tr(h::td(set::colspan(5), $lang->noData)))
            )
        )
    )
);

render();
