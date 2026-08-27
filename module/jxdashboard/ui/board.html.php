<?php
declare(strict_types=1);
namespace zin;

$board     = $board;
$viewName  = $viewName;
$bizLink   = function($method, $biz = '', $dept = '', $status = '') { return createLink('jxdashboard', $method, "bizType={$biz}&dept={$dept}&status={$status}"); };

featureBar
(
    set::current($viewName),
    set::items(array(
        array('id' => 'overview',  'text' => $lang->jxdashboard->overview,  'url' => $bizLink('overview',  $filters['bizType'], $filters['dept'], $filters['status']), 'active' => $viewName == 'overview'),
        array('id' => 'dept',      'text' => $lang->jxdashboard->dept,      'url' => $bizLink('dept',      $filters['bizType'], $filters['dept'], $filters['status']), 'active' => $viewName == 'dept'),
        array('id' => 'portfolio', 'text' => $lang->jxdashboard->portfolio, 'url' => $bizLink('portfolio', $filters['bizType'], $filters['dept'], $filters['status']), 'active' => $viewName == 'portfolio'),
        array('id' => 'meeting',   'text' => $lang->jxdashboard->meeting,   'url' => $bizLink('meeting',   $filters['bizType'], $filters['dept'], $filters['status']), 'active' => $viewName == 'meeting')
    ))
);

div
(
    setClass('jx-dash-filters flex flex-wrap gap-3 p-3'),
    picker(set::name('bizType'), set::items($bizTypes), set::value($filters['bizType']), set::placeholder($lang->jxdashboard->bizType), on::change('window.jxDashChange')),
    picker(set::name('dept'), set::items(array('' => $lang->jxdashboard->all) + $depts), set::value($filters['dept']), set::placeholder($lang->jxdashboard->deptName), on::change('window.jxDashChange'))
);

$cards = div
(
    setClass('jx-kpi-grid'),
    div(setClass('jx-kpi-card'), div(setClass('jx-kpi-label'), $lang->jxdashboard->total), div(setClass('jx-kpi-value'), $board->total)),
    div(setClass('jx-kpi-card is-green'), div(setClass('jx-kpi-label'), $lang->jxdashboard->green), div(setClass('jx-kpi-value'), $board->byHealth['green'] ?? 0)),
    div(setClass('jx-kpi-card is-yellow'), div(setClass('jx-kpi-label'), $lang->jxdashboard->yellow), div(setClass('jx-kpi-value'), $board->byHealth['yellow'] ?? 0)),
    div(setClass('jx-kpi-card is-red'), div(setClass('jx-kpi-label'), $lang->jxdashboard->red), div(setClass('jx-kpi-value'), $board->byHealth['red'] ?? 0)),
    div(setClass('jx-kpi-card'), div(setClass('jx-kpi-label'), $lang->jxdashboard->budget), div(setClass('jx-kpi-value'), number_format((float)$board->budget, 1))),
    div(setClass('jx-kpi-card'), div(setClass('jx-kpi-label'), $lang->jxdashboard->actual), div(setClass('jx-kpi-value'), number_format((float)$board->actual, 1))),
    div(setClass('jx-kpi-card'), div(setClass('jx-kpi-label'), $lang->jxdashboard->delta), div(setClass('jx-kpi-value'), number_format((float)$board->delta, 1)))
);

$bizRows = array();
foreach($board->byBiz as $biz => $count)
{
    $bizRows[] = h::tr(h::td(zget($lang->jxdashboard->bizTypeList, $biz, $biz)), h::td($count));
}

$deptRows = array();
foreach($board->byDept as $name => $stat)
{
    $deptRows[] = h::tr(h::td($name), h::td($stat['count']), h::td(number_format($stat['budget'], 1)), h::td(number_format($stat['actual'], 1)), h::td($stat['red']));
}

$projectRows = array();
$moduleMap = array('registration' => 'jxregistration', 'marketaccess' => 'jxmarketaccess', 'admission' => 'jxadmission');
foreach($board->projects as $project)
{
    $linkModule = $moduleMap[$project->bizType] ?? 'project';
    $linkMethod = $linkModule == 'project' ? 'view' : 'view';
    $idParam    = $linkModule == 'project' ? $project->project : ($project->bizID ?: $project->project);
    $href       = $linkModule == 'project' ? createLink('project', 'view', "projectID={$project->project}") : createLink($linkModule, 'view', "id={$idParam}");
    $projectRows[] = h::tr
    (
        h::td(a(set::href($href), $project->name)),
        h::td(zget($lang->jxdashboard->bizTypeList, $project->bizType, $project->bizType)),
        h::td($project->leadDept),
        h::td(span(setClass('label jx-health-' . $project->health), zget($lang->jxcore->healthList, $project->health))),
        h::td($project->progress . '%'),
        h::td($project->end),
        h::td($project->budget),
        h::td($project->actual ?? 0),
        h::td($project->blocker)
    );
}

$overdueItems = array();
foreach($board->overdue as $item) $overdueItems[] = li(a(set::href(createLink('project', 'view', "projectID={$item->project}")), $item->name . ' · ' . $item->end));
$certItems = array();
foreach($board->certExpiring as $item) $certItems[] = li(a(set::href(createLink('product', 'view', "productID={$item->product}")), $item->name . ' · ' . $item->certValidTo));
$windowItems = array();
foreach($board->windows as $item) $windowItems[] = li(a(set::href(createLink('jxmarketaccess', 'view', "id={$item->id}")), $item->name . ' · ' . $item->windowEnd));
$blockerItems = array();
foreach($board->blockers as $item) $blockerItems[] = li(a(set::href(createLink('project', 'view', "projectID={$item->project}")), $item->name . ' · ' . ($item->blocker ?: $lang->jxdashboard->red)));
$funnelItems = array();
foreach($board->funnel as $st => $count) $funnelItems[] = li(zget($lang->jxcore->statusList, $st, $st) . '：' . $count);

div
(
    setClass('jx-dashboard'),
    $cards,
    $viewName != 'meeting' ? div
    (
        setClass('jx-dash-grid mt-4'),
        div(setClass('jx-dash-panel'), h::h4($lang->jxdashboard->bizType), h::table(setClass('table'), h::tbody($bizRows))),
        div(setClass('jx-dash-panel'), h::h4($lang->jxdashboard->load), h::table(setClass('table'), h::thead(h::tr(h::th('部门'), h::th('项目'), h::th('预算'), h::th('实际'), h::th('风险'))), h::tbody($deptRows))),
        div(setClass('jx-dash-panel'), h::h4($lang->jxdashboard->certs), $certItems ? ul($certItems) : div($lang->noData)),
        div(setClass('jx-dash-panel'), h::h4($lang->jxdashboard->windows), $windowItems ? ul($windowItems) : div($lang->noData)),
        div(setClass('jx-dash-panel'), h::h4($lang->jxdashboard->funnel), $funnelItems ? ul($funnelItems) : div($lang->noData)),
        div(setClass('jx-dash-panel'), h::h4($lang->jxdashboard->blockers), $blockerItems ? ul($blockerItems) : div($lang->noData))
    ) : null,
    ($viewName == 'portfolio' || $viewName == 'meeting' || $viewName == 'overview') ? div
    (
        setClass('jx-dash-panel mt-4'),
        h::h4($viewName == 'meeting' ? $lang->jxdashboard->meeting : $lang->jxdashboard->portfolio),
        h::table
        (
            setClass('table bordered'),
            h::thead(h::tr(h::th('项目'), h::th('类型'), h::th('部门'), h::th('健康度'), h::th('进度'), h::th('完成日'), h::th('预算'), h::th('实际'), h::th('阻塞'))),
            h::tbody($projectRows ?: h::tr(h::td(set::colspan(9), $lang->noData)))
        )
    ) : null,
    $viewName == 'dept' ? div(setClass('jx-dash-panel mt-4'), h::h4($lang->jxdashboard->dept), h::table(setClass('table bordered'), h::thead(h::tr(h::th('部门'), h::th('项目数'), h::th('预算'), h::th('实际'), h::th('风险数'))), h::tbody($deptRows))) : null
);

render();
