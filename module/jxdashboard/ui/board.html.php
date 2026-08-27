<?php
declare(strict_types=1);
namespace zin;

$board    = $board;
$viewName = $viewName;
$bizLink  = function($method, $biz = '', $dept = '', $status = '') { return createLink('jxdashboard', $method, "bizType={$biz}&dept={$dept}&status={$status}"); };
$moduleMap = array('registration' => 'jxregistration', 'marketaccess' => 'jxmarketaccess', 'admission' => 'jxadmission');
$bizColors = array('registration' => '#2563eb', 'marketaccess' => '#0f766e', 'admission' => '#d97706', 'other' => '#64748b');

$projectHref = function($project) use ($moduleMap)
{
    $linkModule = $moduleMap[$project->bizType] ?? 'project';
    if($linkModule == 'project') return createLink('project', 'view', "projectID={$project->project}");
    $idParam = $project->bizID ?: $project->project;
    return createLink($linkModule, 'view', "id={$idParam}");
};

$daysLabel = function($days) use ($lang)
{
    if($days < 0) return sprintf($lang->jxdashboard->expired, abs($days));
    if($days === 0) return '今日到期';
    return sprintf($lang->jxdashboard->daysLeft, $days);
};

$emptyBox = function($text, $link = '', $linkText = '')
{
    return div
    (
        setClass('jx-panel-empty'),
        div(setClass('jx-panel-empty-title'), $text),
        $link ? a(setClass('btn size-sm primary-outline'), set::href($link), $linkText) : null
    );
};

div
(
    setClass('jx-legacy-banner'),
    span($lang->jxdashboard->legacyHint),
    a(set::href(createLink('jxboard', 'overview')), $lang->jxdashboard->gotoNewBoard)
);

toolbar
(
    hasPriv('jxregistration', 'create') ? btn(setClass('primary'), set::icon('plus'), set::url(createLink('jxregistration', 'create')), $lang->jxdashboard->createReg) : null,
    hasPriv('jxmarketaccess', 'create') ? btn(set::url(createLink('jxmarketaccess', 'create')), $lang->jxdashboard->createAcc) : null,
    hasPriv('jxadmission', 'create') ? btn(set::url(createLink('jxadmission', 'create')), $lang->jxdashboard->createAdm) : null
);

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

$isEmpty = !empty($board->isEmpty);
$kpiEmptyClass = $isEmpty ? ' is-empty' : '';
$cards = div
(
    setClass('jx-kpi-grid'),
    div(setClass('jx-kpi-card' . $kpiEmptyClass), div(setClass('jx-kpi-label'), $lang->jxdashboard->total), div(setClass('jx-kpi-value'), $board->total)),
    div(setClass('jx-kpi-card is-green' . $kpiEmptyClass), div(setClass('jx-kpi-label'), $lang->jxdashboard->green), div(setClass('jx-kpi-value'), $board->byHealth['green'] ?? 0)),
    div(setClass('jx-kpi-card is-yellow' . $kpiEmptyClass), div(setClass('jx-kpi-label'), $lang->jxdashboard->yellow), div(setClass('jx-kpi-value'), $board->byHealth['yellow'] ?? 0)),
    div(setClass('jx-kpi-card is-red' . $kpiEmptyClass), div(setClass('jx-kpi-label'), $lang->jxdashboard->red), div(setClass('jx-kpi-value'), $board->byHealth['red'] ?? 0)),
    div(setClass('jx-kpi-card' . $kpiEmptyClass), div(setClass('jx-kpi-label'), $lang->jxdashboard->budget), div(setClass('jx-kpi-value'), number_format((float)$board->budget, 1))),
    div(setClass('jx-kpi-card' . $kpiEmptyClass), div(setClass('jx-kpi-label'), $lang->jxdashboard->actual), div(setClass('jx-kpi-value'), number_format((float)$board->actual, 1))),
    div(setClass('jx-kpi-card' . $kpiEmptyClass), div(setClass('jx-kpi-label'), $lang->jxdashboard->delta), div(setClass('jx-kpi-value'), number_format((float)$board->delta, 1)))
);

$pageEmpty = null;
if($isEmpty)
{
    $title = !empty($board->filteredEmpty) ? $lang->jxdashboard->emptyFilterTitle : $lang->jxdashboard->emptyTitle;
    $desc  = !empty($board->filteredEmpty) ? $lang->jxdashboard->emptyFilterDesc  : $lang->jxdashboard->emptyDesc;
    $pageEmpty = div
    (
        setClass('jx-dash-empty'),
        div(setClass('jx-dash-empty-title'), $title),
        div(setClass('jx-dash-empty-desc'), $desc),
        div
        (
            setClass('jx-dash-empty-actions'),
            !empty($board->filteredEmpty)
                ? a(setClass('btn primary'), set::href($bizLink($viewName)), $lang->jxdashboard->clearFilter)
                : array(
                    a(setClass('btn primary'), set::href(createLink('jxregistration', 'create')), $lang->jxdashboard->createReg),
                    a(setClass('btn'), set::href(createLink('jxmarketaccess', 'create')), $lang->jxdashboard->createAcc),
                    a(setClass('btn'), set::href(createLink('jxadmission', 'create')), $lang->jxdashboard->createAdm)
                )
        )
    );
}

$bizTotal = 0;
foreach($board->byBiz as $count) $bizTotal += (int)$count;
$donutStops = array();
$legendRows = array();
if($bizTotal > 0)
{
    $acc = 0;
    foreach($board->byBiz as $biz => $count)
    {
        $count = (int)$count;
        if($count <= 0) continue;
        $from = $acc / $bizTotal * 360;
        $acc += $count;
        $to = $acc / $bizTotal * 360;
        $color = $bizColors[$biz] ?? $bizColors['other'];
        $donutStops[] = "{$color} {$from}deg {$to}deg";
        $legendRows[] = div
        (
            setClass('jx-legend-row'),
            div(setClass('jx-legend-dot'), setStyle(array('background' => $color))),
            span(zget($lang->jxdashboard->bizTypeList, $biz, $biz)),
            span(setClass('jx-legend-num'), $count)
        );
    }
}
$bizPanel = div
(
    setClass('jx-dash-panel'),
    h::h4($lang->jxdashboard->bizType),
    $bizTotal > 0 ? div
    (
        setClass('jx-donut-wrap'),
        div(setClass('jx-donut'), setStyle(array('background' => 'conic-gradient(' . implode(',', $donutStops) . ')'))),
        div(setClass('jx-donut-legend'), $legendRows)
    ) : $emptyBox($lang->jxdashboard->emptyBiz, createLink('jxregistration', 'create'), $lang->jxdashboard->createReg)
);

$deptRows = array();
foreach($board->byDept as $name => $stat)
{
    $deptRows[] = h::tr
    (
        h::td($name),
        h::td($stat['count']),
        h::td(number_format((float)$stat['budget'], 1)),
        h::td(number_format((float)$stat['actual'], 1)),
        h::td((int)$stat['red'])
    );
}
$loadTable = h::table
(
    setClass('table'),
    h::thead(h::tr(h::th($lang->jxdashboard->colDept), h::th($lang->jxdashboard->colProject), h::th($lang->jxdashboard->colBudget), h::th($lang->jxdashboard->colActual), h::th($lang->jxdashboard->colRisk))),
    h::tbody($deptRows)
);
$loadPanel = div
(
    setClass('jx-dash-panel'),
    h::h4($lang->jxdashboard->load),
    $deptRows ? $loadTable : $emptyBox($lang->jxdashboard->emptyLoad)
);

$certItems = array();
foreach($board->certExpiring as $item)
{
    $days = (int)($item->daysLeft ?? 0);
    $certItems[] = a
    (
        setClass('jx-dash-item'),
        set::href(createLink('product', 'view', "productID={$item->product}")),
        div
        (
            setClass('jx-dash-item-main'),
            div(setClass('jx-dash-item-title'), $item->name ?: ($item->model ?? '')),
            div(setClass('jx-dash-item-meta'), ($item->certNo ? $item->certNo . ' · ' : '') . $item->certValidTo)
        ),
        span(setClass('label ' . ($days < 0 ? 'jx-health-red' : 'jx-health-yellow')), $daysLabel($days))
    );
}
$certPanel = div
(
    setClass('jx-dash-panel'),
    h::h4($lang->jxdashboard->certs),
    $certItems ? div(setClass('jx-dash-list'), $certItems) : $emptyBox($lang->jxdashboard->emptyCerts, createLink('product', 'create'), $lang->jxdashboard->createProduct)
);

$windowItems = array();
$windowDays  = 14;
foreach($board->windows as $item)
{
    $days = (int)($item->daysLeft ?? 0);
    $tag  = $days <= $windowDays ? $lang->jxdashboard->windowSoon : $lang->jxdashboard->windowNext;
    $windowItems[] = a
    (
        setClass('jx-dash-item'),
        set::href(createLink('jxmarketaccess', 'view', "id={$item->id}")),
        div
        (
            setClass('jx-dash-item-main'),
            div(setClass('jx-dash-item-title'), $item->name),
            div(setClass('jx-dash-item-meta'), $item->windowEnd)
        ),
        span(setClass('label ' . ($days < 0 ? 'jx-health-red' : ($days <= $windowDays ? 'jx-health-yellow' : 'jx-health-green'))), $daysLabel($days) . ' · ' . $tag)
    );
}
$windowPanel = div
(
    setClass('jx-dash-panel'),
    h::h4($lang->jxdashboard->windows),
    $windowItems ? div(setClass('jx-dash-list'), $windowItems) : $emptyBox($lang->jxdashboard->emptyWindows, createLink('jxmarketaccess', 'create'), $lang->jxdashboard->createAcc)
);

$funnelMax = 0;
foreach($board->funnel as $count) $funnelMax = max($funnelMax, (int)$count);
$funnelRows = array();
foreach($board->funnel as $name => $count)
{
    $count = (int)$count;
    $pct   = $funnelMax > 0 ? max(8, (int)round($count * 100 / $funnelMax)) : 0;
    if($count === 0) $pct = 0;
    $funnelRows[] = div
    (
        setClass('jx-funnel-row'),
        div(setClass('jx-funnel-meta'), span($name), span($count)),
        div(setClass('jx-funnel-track'), span(setClass('jx-funnel-bar'), setStyle(array('width' => $pct . '%'))))
    );
}
$funnelPanel = div
(
    setClass('jx-dash-panel'),
    h::h4($lang->jxdashboard->funnel),
    !empty($board->admissionCount) ? div(setClass('jx-funnel'), $funnelRows) : $emptyBox($lang->jxdashboard->emptyFunnel, createLink('jxadmission', 'create'), $lang->jxdashboard->createAdm)
);

$overdueItems = array();
foreach($board->overdueStages as $item)
{
    $overdueItems[] = a
    (
        setClass('jx-dash-item'),
        set::href(createLink('project', 'view', "projectID={$item->project}")),
        div
        (
            setClass('jx-dash-item-main'),
            div(setClass('jx-dash-item-title'), $item->name),
            div(setClass('jx-dash-item-meta'), $item->stageName . ' · ' . $item->end)
        ),
        span(setClass('label jx-health-red'), sprintf($lang->jxdashboard->overdueDays, $item->overdueDays))
    );
}
if(!$overdueItems)
{
    foreach($board->overdue as $item)
    {
        $overdueItems[] = a
        (
            setClass('jx-dash-item'),
            set::href($projectHref($item)),
            div
            (
                setClass('jx-dash-item-main'),
                div(setClass('jx-dash-item-title'), $item->name),
                div(setClass('jx-dash-item-meta'), $item->end)
            ),
            span(setClass('label jx-health-red'), sprintf($lang->jxdashboard->overdueDays, $item->overdueDays ?? 0))
        );
    }
}
$overduePanel = div
(
    setClass('jx-dash-panel'),
    h::h4($lang->jxdashboard->overdue),
    $overdueItems ? div(setClass('jx-dash-list'), array_slice($overdueItems, 0, 8)) : $emptyBox($lang->jxdashboard->emptyOverdue)
);

$blockerItems = array();
foreach($board->blockers as $item)
{
    $blockerItems[] = a
    (
        setClass('jx-dash-item'),
        set::href($projectHref($item)),
        div
        (
            setClass('jx-dash-item-main'),
            div(setClass('jx-dash-item-title'), $item->name),
            div(setClass('jx-dash-item-meta'), $item->blocker ?: $lang->jxdashboard->red)
        ),
        span(setClass('label jx-health-red'), zget($lang->jxcore->healthList, $item->health, $item->health))
    );
}
$blockerPanel = div
(
    setClass('jx-dash-panel'),
    h::h4($lang->jxdashboard->blockers),
    $blockerItems ? div(setClass('jx-dash-list'), array_slice($blockerItems, 0, 8)) : $emptyBox($lang->jxdashboard->emptyBlockers)
);

$projectRows = array();
$tableProjects = $board->projects;
if($viewName == 'meeting')
{
    $riskRows = array();
    foreach($board->projects as $project)
    {
        if($project->health != 'green' || !empty($project->blocker)) $riskRows[] = $project;
    }
    if($riskRows) $tableProjects = $riskRows;
}
foreach($tableProjects as $project)
{
    $health = $project->health ?: 'green';
    $pct    = (float)$project->progress;
    $projectRows[] = h::tr
    (
        h::td(a(set::href($projectHref($project)), $project->name)),
        h::td(zget($lang->jxdashboard->bizTypeList, $project->bizType, $project->bizType)),
        h::td($project->leadDept),
        h::td(span(setClass('label jx-health-' . $health), zget($lang->jxcore->healthList, $health))),
        h::td
        (
            div
            (
                setClass('jx-progress'),
                div(setClass('jx-progress-track'), span(setClass('jx-progress-bar is-' . $health), setStyle(array('width' => $pct . '%')))),
                span(setClass('jx-progress-num'), $pct . '%')
            )
        ),
        h::td($project->end),
        h::td($project->budget),
        h::td($project->actual ?? 0),
        h::td($project->blocker)
    );
}

$portfolioTitle = $viewName == 'meeting' ? $lang->jxdashboard->meeting : $lang->jxdashboard->portfolio;
$portfolioPanel = div
(
    setClass('jx-dash-panel is-wide mt-4'),
    h::h4($portfolioTitle),
    $projectRows ? div
    (
        setClass('jx-table-wrap'),
        h::table
        (
            setClass('table bordered'),
            h::thead(h::tr(
                h::th($lang->jxdashboard->colProject),
                h::th($lang->jxdashboard->colType),
                h::th($lang->jxdashboard->colDept),
                h::th($lang->jxdashboard->colHealth),
                h::th($lang->jxdashboard->colProgress),
                h::th($lang->jxdashboard->colEnd),
                h::th($lang->jxdashboard->colBudget),
                h::th($lang->jxdashboard->colActual),
                h::th($lang->jxdashboard->colBlocker)
            )),
            h::tbody($projectRows)
        )
    ) : $emptyBox($lang->jxdashboard->emptyPortfolio, createLink('jxregistration', 'create'), $lang->jxdashboard->createReg)
);

$deptFullPanel = div
(
    setClass('jx-dash-panel is-wide mt-4'),
    h::h4($lang->jxdashboard->dept),
    $deptRows ? h::table
    (
        setClass('table bordered'),
        h::thead(h::tr(h::th($lang->jxdashboard->colDept), h::th($lang->jxdashboard->colCount), h::th($lang->jxdashboard->colBudget), h::th($lang->jxdashboard->colActual), h::th($lang->jxdashboard->riskCount))),
        h::tbody($deptRows)
    ) : $emptyBox($lang->jxdashboard->emptyLoad)
);

$midGrid = null;
if($viewName == 'overview' || $viewName == 'dept')
{
    $midGrid = div(setClass('jx-dash-grid mt-4'), $bizPanel, $loadPanel, $certPanel, $windowPanel, $funnelPanel, $overduePanel, $blockerPanel);
}
elseif($viewName == 'meeting')
{
    $midGrid = div(setClass('jx-dash-grid mt-4'), $overduePanel, $blockerPanel, $certPanel, $windowPanel);
}

div
(
    setClass('jx-dashboard'),
    $cards,
    $pageEmpty,
    $isEmpty ? null : $midGrid,
    (!$isEmpty && ($viewName == 'portfolio' || $viewName == 'meeting' || $viewName == 'overview')) ? $portfolioPanel : null,
    (!$isEmpty && $viewName == 'dept') ? $deptFullPanel : null
);

render();
