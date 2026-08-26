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
        $checks[] = div(setClass('flex items-center gap-2 py-1'),
            a(setClass('btn size-sm ghost ajax-submit'), set::href(createLink('jxcore', 'togglecheck', "checkID={$check->id}&done=" . ($check->done ? 0 : 1))), $check->done ? icon('checked') : icon('circle')),
            span(setClass($check->done ? 'line-through text-gray' : ''), ($check->required ? '* ' : '') . $check->name)
        );
    }
    $actions = array();
    if(in_array($stage->status, array('doing', 'rejected'))) $actions[] = btn(setClass('primary size-sm ajax-submit'), set::url(createLink('jxcore', 'submitstage', "stageID={$stage->id}")), $lang->jxcore->submit);
    if($stage->status == 'submitted') $actions[] = btn(setClass('success size-sm ajax-submit'), set::url(createLink('jxcore', 'approvestage', "stageID={$stage->id}")), $lang->jxcore->approve);
    $stageBlocks[] = div(setClass('border rounded-md p-3 mb-3 bg-white'),
        div(setClass('flex justify-between mb-2'), div(setClass('font-bold'), $stage->order . '. ' . $stage->name, span(setClass('label circle ml-2'), zget($lang->jxcore->statusList, $stage->status))), span(setClass('text-gray'), formatTime($stage->begin, DT_DATE1) . ' ~ ' . formatTime($stage->end, DT_DATE1))),
        $checks, div(setClass('mt-2 flex gap-2'), $actions)
    );
}
$costRows = array();
foreach($costs as $cost) $costRows[] = h::tr(h::td($cost->occurDate), h::td($cost->dept), h::td($cost->category), h::td($cost->amount), h::td($cost->desc));

detailHeader
(
    to::prefix(backBtn($lang->goback, setClass('secondary'), set::icon('back')), entityLabel(set::entityID($row->id), set::text($row->name), set::level(1)), span(setClass('label ' . zget($healthLabel, $extraHealth, '')), zget($lang->jxcore->healthList, $extraHealth))),
    to::suffix(
        $row->project ? btn(set::url(createLink('project', 'view', "projectID={$row->project}")), $lang->jxcore->openProject) : null,
        $row->project ? btn(set::url(createLink('doc', 'projectSpace', "objectID={$row->project}")), $lang->jxcore->openDoc) : null,
        hasPriv('jxmarketaccess', 'edit') ? btn(set::icon('edit'), set::url(createLink('jxmarketaccess', 'edit', "id={$row->id}")), $lang->edit) : null
    )
);
detailBody(sectionList(
    section(set::title($lang->jxmarketaccess->common), tableData(
        item(set::name($lang->jxmarketaccess->product), $product ? $product->name : '-'),
        item(set::name($lang->jxmarketaccess->type), zget($lang->jxmarketaccess->typeList, $row->type)),
        item(set::name($lang->jxmarketaccess->region), $row->region),
        item(set::name($lang->jxmarketaccess->platform), $row->platform),
        item(set::name($lang->jxmarketaccess->package), $row->package),
        item(set::name($lang->jxmarketaccess->windowEnd), $row->windowEnd),
        item(set::name($lang->jxmarketaccess->result), zget($lang->jxmarketaccess->resultList, $row->result)),
        item(set::name($lang->jxmarketaccess->requireCert), $row->requireCert ? $lang->yes : $lang->no),
        item(set::name($lang->jxcore->progress), ($extra?->progress ?? 0) . '%'),
        item(set::name($lang->jxcore->blocker), $extra?->blocker ?? '-')
    )),
    section(set::title($lang->jxmarketaccess->stages), $stageBlocks),
    section(set::title($lang->jxmarketaccess->costs),
        div(setClass('mb-2 flex gap-4 items-center'),
            span($lang->jxcore->budget . '：' . ($summary->budget ?? $row->budget)),
            span($lang->jxcore->actual . '：' . ($summary->actual ?? 0)),
            $row->project ? btn(setClass('primary size-sm'), set::url(createLink('jxcore', 'addcost', "projectID={$row->project}")), set('data-toggle', 'modal'), $lang->jxcore->addcost) : null
        ),
        h::table(setClass('table bordered'), h::thead(h::tr(h::th($lang->jxcore->occurDate), h::th($lang->jxcore->dept), h::th($lang->jxcore->category), h::th($lang->jxcore->amount), h::th($lang->jxcore->desc))), h::tbody($costRows ?: h::tr(h::td(set::colspan(5), $lang->noData))))
    )
));
render();
