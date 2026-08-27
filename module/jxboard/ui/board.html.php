<?php
declare(strict_types=1);
namespace zin;

$board    = $board;
$viewName = $viewName;
$deptVal  = (string)$filters['dept'];
$productVal = (int)$filters['product'];
$statusVal  = (string)$filters['status'];
$warnDays = (int)($config->jxboard->warnDays ?? 14);

$boardLink = function($method, $dept = '', $product = 0, $status = '')
{
    return createLink('jxboard', $method, "dept={$dept}&product={$product}&status={$status}");
};

$statusColors  = array('wait' => '#64748b', 'doing' => '#2563eb', 'suspended' => '#d97706', 'closed' => '#059669');
$productColors = array('#2563eb', '#0f766e', '#d97706', '#7c3aed', '#db2777', '#0891b2', '#65a30d', '#ea580c');

$daysLabel = function($days) use ($lang)
{
    if($days < 0) return sprintf($lang->jxboard->expired, abs($days));
    if($days === 0) return $lang->jxboard->dueToday;
    return sprintf($lang->jxboard->daysLeft, $days);
};

$emptyBox = function($text, $link = '', $linkText = '')
{
    return div
    (
        setClass('pm-panel-empty'),
        div(setClass('pm-panel-empty-title'), $text),
        $link ? a(setClass('btn size-sm primary-outline'), set::href($link), $linkText) : null
    );
};

$donutPanel = function($title, $items, $colors, $emptyText, $emptyLink = '', $emptyLinkText = '') use ($emptyBox)
{
    $total = 0;
    foreach($items as $row)
    {
        $total += is_array($row) ? (int)($row['count'] ?? 0) : (int)$row;
    }
    if($total <= 0) return div(setClass('pm-dash-panel is-chart'), h::h4($title), $emptyBox($emptyText, $emptyLink, $emptyLinkText));

    $acc = 0;
    $stops = array();
    $legend = array();
    $index = 0;
    foreach($items as $key => $row)
    {
        $palette = array_values($colors);
        if(is_array($row))
        {
            $label = $row['name'] ?? (string)$key;
            $count = (int)($row['count'] ?? 0);
        }
        else
        {
            $label = (string)$key;
            $count = (int)$row;
        }
        $color = $colors[$key] ?? $palette[$index % count($palette)];
        $index++;
        if($count <= 0) continue;
        $from = $acc / $total * 360;
        $acc += $count;
        $to = $acc / $total * 360;
        $stops[] = "{$color} {$from}deg {$to}deg";
        $legend[] = div
        (
            setClass('pm-legend-row'),
            div(setClass('pm-legend-dot'), setStyle(array('background' => $color))),
            span($label),
            span(setClass('pm-legend-num'), $count)
        );
    }

    return div
    (
        setClass('pm-dash-panel is-chart'),
        h::h4($title),
        div
        (
            setClass('pm-donut-wrap'),
            div(setClass('pm-donut'), setStyle(array('background' => 'conic-gradient(' . implode(',', $stops) . ')'))),
            div(setClass('pm-donut-legend'), $legend)
        )
    );
};

$panelCount = function($count, $alert = false)
{
    $count = (int)$count;
    if($count <= 0) return null;
    return span(setClass('pm-panel-count' . ($alert ? ' is-alert' : '')), $count);
};

div
(
    setClass('pm-dash-filters flex flex-wrap gap-3 p-3'),
    picker(set::name('dept'), set::items(array('' => $lang->jxboard->all, '0' => $lang->jxboard->unassigned) + $depts), set::value($deptVal), set::placeholder($lang->jxboard->deptName), on::change('window.jxBoardChange')),
    picker(set::name('product'), set::items(array('0' => $lang->jxboard->allProducts) + $products), set::value((string)$productVal), set::placeholder($lang->jxboard->product), on::change('window.jxBoardChange')),
    picker(set::name('status'), set::items($statusItems), set::value($statusVal), set::placeholder($lang->jxboard->status), on::change('window.jxBoardChange'))
);

$isEmpty = !empty($board->isEmpty);
$kpiEmptyClass = $isEmpty ? ' is-empty' : '';
$cards = div
(
    setClass('pm-kpi-grid'),
    div(setClass('pm-kpi-card' . $kpiEmptyClass), div(setClass('pm-kpi-label'), $lang->jxboard->total), div(setClass('pm-kpi-value'), $board->total)),
    div(setClass('pm-kpi-card is-green' . $kpiEmptyClass), div(setClass('pm-kpi-label'), $lang->jxboard->green), div(setClass('pm-kpi-value'), $board->byHealth['green'] ?? 0)),
    div(setClass('pm-kpi-card is-yellow' . $kpiEmptyClass), div(setClass('pm-kpi-label'), $lang->jxboard->yellow), div(setClass('pm-kpi-value'), $board->byHealth['yellow'] ?? 0)),
    div(setClass('pm-kpi-card is-red' . $kpiEmptyClass), div(setClass('pm-kpi-label'), $lang->jxboard->red), div(setClass('pm-kpi-value'), $board->byHealth['red'] ?? 0)),
    div(setClass('pm-kpi-card' . $kpiEmptyClass), div(setClass('pm-kpi-label'), $lang->jxboard->budget), div(setClass('pm-kpi-value'), number_format((float)$board->budget, 1))),
    div(setClass('pm-kpi-card' . $kpiEmptyClass), div(setClass('pm-kpi-label'), $lang->jxboard->estimate), div(setClass('pm-kpi-value'), number_format((float)$board->estimate, 1))),
    div(setClass('pm-kpi-card' . $kpiEmptyClass), div(setClass('pm-kpi-label'), $lang->jxboard->consumed), div(setClass('pm-kpi-value'), number_format((float)$board->consumed, 1))),
    div(setClass('pm-kpi-card is-red' . $kpiEmptyClass), div(setClass('pm-kpi-label'), $lang->jxboard->overdueTasks), div(setClass('pm-kpi-value'), $board->overdueTaskCount))
);

$pageEmpty = null;
if($isEmpty)
{
    $title = !empty($board->filteredEmpty) ? $lang->jxboard->emptyFilterTitle : $lang->jxboard->emptyTitle;
    $desc  = !empty($board->filteredEmpty) ? $lang->jxboard->emptyFilterDesc  : $lang->jxboard->emptyDesc;
    $pageEmpty = div
    (
        setClass('pm-dash-empty'),
        div(setClass('pm-dash-empty-title'), $title),
        div(setClass('pm-dash-empty-desc'), $desc),
        !empty($board->filteredEmpty)
            ? div(setClass('pm-dash-empty-actions'), a(setClass('btn primary'), set::href($boardLink($viewName)), $lang->jxboard->clearFilter))
            : null
    );
}

$needCharts   = !$isEmpty && $viewName == 'overview';
$needLists    = !$isEmpty && ($viewName == 'overview' || $viewName == 'meeting');
$needRisk     = !$isEmpty && in_array($viewName, array('overview', 'meeting', 'dept'), true);
$needTable    = !$isEmpty && in_array($viewName, array('overview', 'portfolio', 'meeting'), true);
$needDeptView = !$isEmpty && $viewName == 'dept';

$statusPanel = $productPanel = $overduePanel = $duePanel = $funnelPanel = $riskPanel = $portfolioPanel = $deptFullPanel = $midGrid = null;

if($needCharts)
{
    $statusItemsForDonut = array();
    foreach($board->byStatus as $key => $count)
    {
        $statusItemsForDonut[$key] = array('name' => zget($lang->project->statusList, $key, $key), 'count' => (int)$count);
    }
    $statusPanel  = $donutPanel($lang->jxboard->projectStatus, $statusItemsForDonut, $statusColors, $lang->jxboard->emptyStatus, createLink('project', 'create'), $lang->jxboard->createProject);
    $productPanel = $donutPanel($lang->jxboard->productDist, $board->byProduct, $productColors, $lang->jxboard->emptyProduct, createLink('product', 'create'), $lang->jxboard->createProduct);

    $funnelMax = 0;
    foreach($board->taskFunnel as $count) $funnelMax = max($funnelMax, (int)$count);
    $funnelRows = array();
    $funnelTotal = 0;
    foreach($board->taskFunnel as $key => $count)
    {
        $count = (int)$count;
        $funnelTotal += $count;
        $pct = $funnelMax > 0 ? max(8, (int)round($count * 100 / $funnelMax)) : 0;
        if($count === 0) $pct = 0;
        $funnelRows[] = div
        (
            setClass('pm-funnel-row'),
            div(setClass('pm-funnel-meta'), span(zget($lang->jxboard->funnelList, $key, $key)), span($count)),
            div(setClass('pm-funnel-track'), span(setClass('pm-funnel-bar'), setStyle(array('width' => $pct . '%'))))
        );
    }
    $funnelPanel = div
    (
        setClass('pm-dash-panel is-chart'),
        h::h4($lang->jxboard->taskFunnel),
        $funnelTotal > 0 ? div(setClass('pm-funnel'), $funnelRows) : $emptyBox($lang->jxboard->emptyFunnel, createLink('project', 'browse'), $lang->jxboard->createProject)
    );
}

if($needLists)
{
    $overdueItems = array();
    foreach($board->overdueTasks as $item)
    {
        $overdueItems[] = a
        (
            setClass('pm-dash-item'),
            set::href(createLink('task', 'view', "taskID={$item->id}")),
            div
            (
                setClass('pm-dash-item-main'),
                div(setClass('pm-dash-item-title'), $item->name),
                div(setClass('pm-dash-item-meta'), $item->projectName . ' · ' . ($item->assignedName ?: $item->assignedTo) . ' · ' . $item->deadline)
            ),
            span(setClass('label pm-health-red'), sprintf($lang->jxboard->overdueDays, $item->overdueDays))
        );
    }
    $overduePanel = div
    (
        setClass('pm-dash-panel is-list'),
        h::h4($lang->jxboard->overdueTasks, $panelCount($board->overdueTaskCount, true)),
        $overdueItems ? div(setClass('pm-dash-list'), $overdueItems) : $emptyBox($lang->jxboard->emptyOverdue)
    );

    $dueItems = array();
    foreach($board->dueExecutions as $item)
    {
        $days = (int)$item->daysLeft;
        $dueItems[] = a
        (
            setClass('pm-dash-item'),
            set::href(createLink('execution', 'task', "executionID={$item->id}")),
            div
            (
                setClass('pm-dash-item-main'),
                div(setClass('pm-dash-item-title'), $item->name),
                div(setClass('pm-dash-item-meta'), $item->projectName . ' · ' . $item->end)
            ),
            span(setClass('label ' . ($days < 0 ? 'pm-health-red' : 'pm-health-yellow')), $daysLabel($days))
        );
    }
    $duePanel = div
    (
        setClass('pm-dash-panel is-list'),
        h::h4($lang->jxboard->dueExecutions, $panelCount(count($dueItems))),
        $dueItems ? div(setClass('pm-dash-list'), $dueItems) : $emptyBox(sprintf($lang->jxboard->emptyDueExec, $warnDays))
    );

}

if($needRisk)
{
    $riskItems = array();
    $riskTotal = count($board->riskProjects);
    foreach(array_slice($board->riskProjects, 0, 8) as $item)
    {
        $health = $item->health ?: 'green';
        $riskItems[] = a
        (
            setClass('pm-dash-item'),
            set::href(createLink('project', 'view', "projectID={$item->id}")),
            div
            (
                setClass('pm-dash-item-main'),
                div(setClass('pm-dash-item-title'), $item->name),
                div(setClass('pm-dash-item-meta'), $item->blocker ?: zget($lang->jxboard->healthList, $health, $health))
            ),
            span(setClass('label pm-health-' . $health), zget($lang->jxboard->healthList, $health, $health))
        );
    }
    $riskPanel = div
    (
        setClass('pm-dash-panel is-list'),
        h::h4($lang->jxboard->riskProjects, $panelCount($riskTotal, true)),
        $riskItems ? div(setClass('pm-dash-list'), $riskItems) : $emptyBox($lang->jxboard->emptyRisks)
    );
}

$buildProjectRows = function($list) use ($lang)
{
    $rows = array();
    foreach($list as $project)
    {
        $health = $project->health ?: 'green';
        $pct    = (float)$project->progress;
        $rows[] = h::tr
        (
            h::td(a(set::href(createLink('project', 'view', "projectID={$project->id}")), $project->name)),
            h::td($project->productNames),
            h::td($project->pmName),
            h::td($project->deptName),
            h::td(span(setClass('label pm-health-' . $health), zget($lang->jxboard->healthList, $health, $health))),
            h::td
            (
                div
                (
                    setClass('pm-progress'),
                    div(setClass('pm-progress-track'), span(setClass('pm-progress-bar is-' . $health), setStyle(array('width' => $pct . '%')))),
                    span(setClass('pm-progress-num'), $pct . '%')
                )
            ),
            h::td($project->taskDone . '/' . $project->taskTotal),
            h::td(number_format((float)$project->consumed, 1) . ' / ' . number_format((float)$project->estimate, 1)),
            h::td(number_format((float)$project->budget, 1)),
            h::td($project->end)
        );
    }
    return $rows;
};

if($needTable)
{
    $tableProjects = $viewName == 'meeting' ? $board->riskProjects : $board->projects;
    $projectRows = $buildProjectRows($tableProjects);
    $portfolioTitle = $viewName == 'meeting' ? $lang->jxboard->meeting : $lang->jxboard->portfolio;
    $portfolioPanel = div
    (
        setClass('pm-dash-panel is-wide mt-4'),
        h::h4($portfolioTitle),
        $projectRows ? div
        (
            setClass('pm-table-wrap'),
            h::table
            (
                setClass('table bordered'),
                h::thead(h::tr(
                    h::th($lang->jxboard->colProject),
                    h::th($lang->jxboard->colProduct),
                    h::th($lang->jxboard->colPM),
                    h::th($lang->jxboard->colDept),
                    h::th($lang->jxboard->colHealth),
                    h::th($lang->jxboard->colProgress),
                    h::th($lang->jxboard->colTask),
                    h::th($lang->jxboard->colHours),
                    h::th($lang->jxboard->colBudget),
                    h::th($lang->jxboard->colEnd)
                )),
                h::tbody($projectRows)
            )
        ) : $emptyBox($lang->jxboard->emptyPortfolio, createLink('project', 'create'), $lang->jxboard->createProject)
    );
}

if($needDeptView)
{
    $deptRows = array();
    foreach($board->byDept as $name => $stat)
    {
        $deptRows[] = h::tr
        (
            h::td($name),
            h::td($stat['count']),
            h::td(number_format((float)$stat['budget'], 1)),
            h::td(number_format((float)$stat['estimate'], 1)),
            h::td(number_format((float)$stat['consumed'], 1)),
            h::td((int)$stat['overdue']),
            h::td((int)$stat['red'])
        );
    }
    $deptFullPanel = div
    (
        setClass('pm-dash-panel is-wide mt-4'),
        h::h4($lang->jxboard->dept),
        $deptRows ? array(
            h::table
            (
                setClass('table bordered'),
                h::thead(h::tr(
                    h::th($lang->jxboard->colDept),
                    h::th($lang->jxboard->colCount),
                    h::th($lang->jxboard->colBudget),
                    h::th($lang->jxboard->colEstimate),
                    h::th($lang->jxboard->colConsumed),
                    h::th($lang->jxboard->colOverdue),
                    h::th($lang->jxboard->riskCount)
                )),
                h::tbody($deptRows)
            ),
            !empty($board->hasUnassignedDept) ? div(setClass('pm-dash-tip'), $lang->jxboard->unassignedTip) : null
        ) : $emptyBox($lang->jxboard->emptyLoad)
    );
}

if($viewName == 'overview' && !$isEmpty)
{
    $midGrid = div
    (
        setClass('pm-dash-mid mt-4'),
        div(setClass('pm-dash-grid is-charts'), $statusPanel, $productPanel, $funnelPanel),
        div(setClass('pm-dash-grid is-lists'), $overduePanel, $duePanel, $riskPanel)
    );
}
elseif($viewName == 'meeting' && !$isEmpty)
{
    $midGrid = div(setClass('pm-dash-grid is-lists mt-4'), $riskPanel, $overduePanel, $duePanel);
}
elseif($viewName == 'dept' && !$isEmpty)
{
    $midGrid = div(setClass('pm-dash-grid is-lists is-single mt-4'), $riskPanel);
}

div
(
    setClass('pm-dashboard'),
    setID('pmDashboard-' . $viewName),
    $cards,
    $pageEmpty,
    $isEmpty ? null : $midGrid,
    (!$isEmpty && ($viewName == 'portfolio' || $viewName == 'meeting' || $viewName == 'overview')) ? $portfolioPanel : null,
    (!$isEmpty && $viewName == 'dept') ? $deptFullPanel : null
);

render();
