<?php
declare(strict_types=1);
namespace zin;

$board    = $board;
$viewName = $viewName;
$deptVal  = (string)$filters['dept'];
$productVal = (int)$filters['product'];
$statusVal  = (string)$filters['status'];
$healthVal  = (string)($filters['health'] ?? '');
$periodVal  = (string)($filters['period'] ?? 'month');
$beginVal   = (string)($filters['begin'] ?? '');
$endVal     = (string)($filters['end'] ?? '');
$focusVal   = (string)($filters['focus'] ?? '');
$warnDays   = (int)($config->jxboard->warnDays ?? 14);
$certDays   = (int)($config->jenshin->certWarnDays ?? 90);

$filterQuery = function($overrides = array()) use ($filters)
{
    $f = array_merge($filters, $overrides);
    $begin = preg_replace('/-/', '', (string)($f['begin'] ?? ''));
    $end   = preg_replace('/-/', '', (string)($f['end'] ?? ''));
    return sprintf(
        'dept=%s&product=%s&status=%s&health=%s&period=%s&begin=%s&end=%s&focus=%s',
        $f['dept'] ?? '',
        (int)($f['product'] ?? 0),
        $f['status'] ?? '',
        $f['health'] ?? '',
        $f['period'] ?? 'month',
        $begin,
        $end,
        $f['focus'] ?? ''
    );
};

$boardLink = function($method, $overrides = array()) use ($filterQuery)
{
    return createLink('jxboard', $method, $filterQuery($overrides));
};

$clearLink = $boardLink($viewName, array(
    'dept' => '', 'product' => 0, 'status' => '', 'health' => '',
    'period' => 'month', 'begin' => '', 'end' => '', 'focus' => ''
));

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

$donutPanel = function($title, $items, $colors, $emptyText, $emptyLink = '', $emptyLinkText = '', $itemLink = null) use ($emptyBox)
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
        $href = is_callable($itemLink) ? $itemLink($key, is_array($row) ? $row : array('name' => $label, 'count' => $count)) : '';
        $legend[] = $href
            ? a(setClass('pm-legend-row'), set::href($href), div(setClass('pm-legend-dot'), setStyle(array('background' => $color))), span($label), span(setClass('pm-legend-num'), $count))
            : div(setClass('pm-legend-row'), div(setClass('pm-legend-dot'), setStyle(array('background' => $color))), span($label), span(setClass('pm-legend-num'), $count));
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

$listHeader = function($title, $count, $alert, $focusKey, $shown) use ($lang, $boardLink, $viewName, $focusVal, $panelCount)
{
    $viewAll = null;
    if((int)$count > count($shown) && $focusVal !== $focusKey)
    {
        $viewAll = a(setClass('pm-view-all'), set::href($boardLink($viewName, array('focus' => $focusKey))), $lang->jxboard->viewAll);
    }
    return h::h4($title, $panelCount($count, $alert), $viewAll);
};

$kpiCard = function($label, $value, $href, $extraClass = '')
{
    $class = 'pm-kpi-card' . $extraClass;
    if($href)
    {
        return a(setClass($class . ' is-link'), set::href($href), div(setClass('pm-kpi-label'), $label), div(setClass('pm-kpi-value'), $value));
    }
    return div(setClass($class), div(setClass('pm-kpi-label'), $label), div(setClass('pm-kpi-value'), $value));
};

$healthHref = function($health) use ($boardLink, $viewName, $healthVal)
{
    return $boardLink($viewName, array('health' => $healthVal === $health ? '' : $health, 'focus' => ''));
};

$portfolioHref = $boardLink('portfolio', array('focus' => ''));
$overdueHref   = $boardLink('meeting', array('focus' => 'overdue'));

div
(
    setClass('pm-dash-filters flex flex-wrap gap-3 p-3'),
    picker(set::name('dept'), set::items(array('' => $lang->jxboard->all, '0' => $lang->jxboard->unassigned) + $depts), set::value($deptVal), set::placeholder($lang->jxboard->deptName), on::change('window.jxBoardChange')),
    picker(set::name('product'), set::items(array('0' => $lang->jxboard->allProducts) + $products), set::value((string)$productVal), set::placeholder($lang->jxboard->product), on::change('window.jxBoardChange')),
    picker(set::name('status'), set::items($statusItems), set::value($statusVal), set::placeholder($lang->jxboard->status), on::change('window.jxBoardChange')),
    picker(set::name('health'), set::items($healthItems), set::value($healthVal), set::placeholder($lang->jxboard->health), on::change('window.jxBoardChange')),
    picker(set::name('period'), set::items($periodItems), set::value($periodVal), set::placeholder($lang->jxboard->period), on::change('window.jxBoardChange')),
    div
    (
        setClass('pm-dash-custom-dates' . ($periodVal === 'custom' ? '' : ' hidden')),
        h::input(setClass('form-control pm-date-input'), set(array('type' => 'date', 'name' => 'begin', 'value' => $beginVal, 'title' => $lang->jxboard->beginDate)), on::change('window.jxBoardChange')),
        h::input(setClass('form-control pm-date-input'), set(array('type' => 'date', 'name' => 'end', 'value' => $endVal, 'title' => $lang->jxboard->endDate)), on::change('window.jxBoardChange'))
    )
);

$isEmpty = !empty($board->isEmpty);
$kpiEmptyClass = $isEmpty ? ' is-empty' : '';
$cards = div
(
    setClass('pm-kpi-grid'),
    $kpiCard($lang->jxboard->total, $board->total, $isEmpty ? '' : $portfolioHref, $kpiEmptyClass),
    $kpiCard($lang->jxboard->green, $board->byHealth['green'] ?? 0, $healthHref('green'), ' is-green' . $kpiEmptyClass . ($healthVal === 'green' ? ' is-active' : '')),
    $kpiCard($lang->jxboard->yellow, $board->byHealth['yellow'] ?? 0, $healthHref('yellow'), ' is-yellow' . $kpiEmptyClass . ($healthVal === 'yellow' ? ' is-active' : '')),
    $kpiCard($lang->jxboard->red, $board->byHealth['red'] ?? 0, $healthHref('red'), ' is-red' . $kpiEmptyClass . ($healthVal === 'red' ? ' is-active' : '')),
    $kpiCard($lang->jxboard->budget, number_format((float)$board->budget, 1), $isEmpty ? '' : $portfolioHref, $kpiEmptyClass),
    $kpiCard($lang->jxboard->estimate, number_format((float)$board->estimate, 1), $isEmpty ? '' : $portfolioHref, $kpiEmptyClass),
    $kpiCard($lang->jxboard->consumed, number_format((float)$board->consumed, 1), $isEmpty ? '' : $portfolioHref, $kpiEmptyClass),
    $kpiCard($lang->jxboard->hoursLeft, number_format((float)$board->left, 1), $isEmpty ? '' : $portfolioHref, $kpiEmptyClass),
    $kpiCard($lang->jxboard->overdueTasks, $board->overdueTaskCount, $overdueHref, ' is-red' . $kpiEmptyClass)
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
            ? div(setClass('pm-dash-empty-actions'), a(setClass('btn primary'), set::href($clearLink), $lang->jxboard->clearFilter))
            : null
    );
}

$needCharts   = !$isEmpty && $viewName == 'overview';
$needLists    = !$isEmpty && ($viewName == 'overview' || $viewName == 'meeting');
$needRisk     = !$isEmpty && in_array($viewName, array('overview', 'meeting', 'dept'), true);
$needCerts    = !$isEmpty && ($viewName == 'overview' || $viewName == 'meeting');
$needTable    = !$isEmpty && in_array($viewName, array('overview', 'portfolio', 'meeting'), true);
$needDeptView = !$isEmpty && $viewName == 'dept';
$needLoad     = !$isEmpty && ($viewName == 'overview' || $viewName == 'dept');
$needTrend    = !$isEmpty && $viewName == 'overview';

$statusPanel = $productPanel = $overduePanel = $duePanel = $funnelPanel = $riskPanel = $certPanel = $portfolioPanel = $deptFullPanel = $loadPanel = $trendPanel = $midGrid = $meetingActions = null;

if($viewName == 'meeting')
{
    $exportUrl = createLink('jxboard', 'meeting', $filterQuery() . '&export=csv');
    $meetingActions = div
    (
        setClass('pm-dash-actions'),
        a(setClass('btn size-sm pm-print-board'), set::href('#print'), $lang->jxboard->print),
        a(setClass('btn size-sm primary-outline pm-export-csv'), set::href($exportUrl), setData('load', false), $lang->jxboard->exportCsv)
    );
}

if($needCharts)
{
    $statusItemsForDonut = array();
    foreach($board->byStatus as $key => $count)
    {
        $statusItemsForDonut[$key] = array('name' => zget($lang->project->statusList, $key, $key), 'count' => (int)$count);
    }
    $statusPanel  = $donutPanel($lang->jxboard->projectStatus, $statusItemsForDonut, $statusColors, $lang->jxboard->emptyStatus, createLink('project', 'create'), $lang->jxboard->createProject, function($key) use ($boardLink, $viewName, $statusVal)
    {
        return $boardLink($viewName, array('status' => $statusVal === (string)$key ? '' : (string)$key, 'focus' => ''));
    });
    $productPanel = $donutPanel($lang->jxboard->productDist, $board->byProduct, $productColors, $lang->jxboard->emptyProduct, createLink('product', 'create'), $lang->jxboard->createProduct, function($key, $row) use ($boardLink, $viewName, $productVal)
    {
        $id = (int)($row['id'] ?? $key);
        return $boardLink($viewName, array('product' => $id === $productVal ? 0 : $id, 'focus' => ''));
    });

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

if($needTrend)
{
    $trends = $board->trends ?? null;
    $buckets = $trends && !empty($trends->buckets) ? $trends->buckets : array();
    $taskMax = 0;
    $hourMax = 0;
    foreach($buckets as $bucket)
    {
        $taskMax = max($taskMax, (int)$bucket['tasks']);
        $hourMax = max($hourMax, (float)$bucket['hours']);
    }
    $taskRows = array();
    $hourRows = array();
    foreach($buckets as $bucket)
    {
        $taskPct = $taskMax > 0 ? max(6, (int)round((int)$bucket['tasks'] * 100 / $taskMax)) : 0;
        $hourPct = $hourMax > 0 ? max(6, (int)round((float)$bucket['hours'] * 100 / $hourMax)) : 0;
        if((int)$bucket['tasks'] === 0) $taskPct = 0;
        if((float)$bucket['hours'] <= 0) $hourPct = 0;
        $taskRows[] = div
        (
            setClass('pm-funnel-row'),
            div(setClass('pm-funnel-meta'), span($bucket['label']), span((int)$bucket['tasks'])),
            div(setClass('pm-funnel-track'), span(setClass('pm-funnel-bar'), setStyle(array('width' => $taskPct . '%'))))
        );
        $hourRows[] = div
        (
            setClass('pm-funnel-row'),
            div(setClass('pm-funnel-meta'), span($bucket['label']), span(number_format((float)$bucket['hours'], 1))),
            div(setClass('pm-funnel-track'), span(setClass('pm-funnel-bar is-hours'), setStyle(array('width' => $hourPct . '%'))))
        );
    }
    $rangeLabel = ($trends && $trends->begin && $trends->end) ? ($trends->begin . ' ~ ' . $trends->end) : '';
    $trendPanel = div
    (
        setClass('pm-dash-panel is-wide is-chart mt-4'),
        h::h4($lang->jxboard->trend, $rangeLabel ? span(setClass('pm-panel-sub'), $rangeLabel) : null),
        !empty($trends->hasData)
            ? div(setClass('pm-trend-wrap'), div(setClass('pm-trend-col'), div(setClass('pm-trend-title'), $lang->jxboard->trendTasks), div(setClass('pm-funnel'), $taskRows)), div(setClass('pm-trend-col'), div(setClass('pm-trend-title'), $lang->jxboard->trendHours), div(setClass('pm-funnel'), $hourRows)))
            : $emptyBox($lang->jxboard->emptyTrend)
    );
}

if($needLoad)
{
    $loadRows = array();
    $loadMax = 0;
    foreach($board->byDept as $stat)
    {
        $loadMax = max($loadMax, (float)$stat['estimate'], (float)$stat['consumed']);
    }
    foreach($board->byDept as $name => $stat)
    {
        $estPct = $loadMax > 0 ? (int)round((float)$stat['estimate'] * 100 / $loadMax) : 0;
        $conPct = $loadMax > 0 ? (int)round((float)$stat['consumed'] * 100 / $loadMax) : 0;
        $deptID = (string)($stat['id'] ?? '0');
        $loadRows[] = div
        (
            setClass('pm-load-row'),
            a(setClass('pm-load-name'), set::href($boardLink($viewName, array('dept' => $deptID, 'focus' => ''))), $name),
            div
            (
                setClass('pm-load-bars'),
                div(setClass('pm-funnel-track'), span(setClass('pm-funnel-bar'), setStyle(array('width' => $estPct . '%')), set::title($lang->jxboard->estimate . ' ' . number_format((float)$stat['estimate'], 1)))),
                div(setClass('pm-funnel-track'), span(setClass('pm-funnel-bar is-hours'), setStyle(array('width' => $conPct . '%')), set::title($lang->jxboard->consumed . ' ' . number_format((float)$stat['consumed'], 1))))
            ),
            span(setClass('pm-load-num'), number_format((float)$stat['consumed'], 1) . ' / ' . number_format((float)$stat['estimate'], 1))
        );
    }
    $loadPanel = div
    (
        setClass('pm-dash-panel is-wide is-chart mt-4'),
        h::h4($lang->jxboard->load),
        $loadRows ? div(setClass('pm-load'), $loadRows) : $emptyBox($lang->jxboard->emptyLoad)
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
        setClass('pm-dash-panel is-list' . ($focusVal === 'overdue' ? ' is-focus' : '')),
        setID('pm-focus-overdue'),
        $listHeader($lang->jxboard->overdueTasks, $board->overdueTaskCount, true, 'overdue', $board->overdueTasks),
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
    $dueCount = (int)($board->dueExecutionCount ?? count($board->dueExecutions));
    $duePanel = div
    (
        setClass('pm-dash-panel is-list' . ($focusVal === 'due' ? ' is-focus' : '')),
        setID('pm-focus-due'),
        $listHeader($lang->jxboard->dueExecutions, $dueCount, false, 'due', $board->dueExecutions),
        $dueItems ? div(setClass('pm-dash-list'), $dueItems) : $emptyBox(sprintf($lang->jxboard->emptyDueExec, $warnDays))
    );
}

if($needRisk)
{
    $riskItems = array();
    $riskShown = !empty($board->riskProjectsView) ? $board->riskProjectsView : $board->riskProjects;
    $riskTotal = (int)($board->riskCount ?? count($board->riskProjects));
    foreach($riskShown as $item)
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
        setClass('pm-dash-panel is-list' . ($focusVal === 'risk' ? ' is-focus' : '')),
        setID('pm-focus-risk'),
        $listHeader($lang->jxboard->riskProjects, $riskTotal, true, 'risk', $riskShown),
        $riskItems ? div(setClass('pm-dash-list'), $riskItems) : $emptyBox($lang->jxboard->emptyRisks)
    );
}

if($needCerts)
{
    $certItems = array();
    foreach($board->certExpiring as $item)
    {
        $days = (int)$item->daysLeft;
        $certItems[] = a
        (
            setClass('pm-dash-item'),
            set::href(createLink('product', 'view', "productID={$item->product}")),
            div
            (
                setClass('pm-dash-item-main'),
                div(setClass('pm-dash-item-title'), $item->productName),
                div(setClass('pm-dash-item-meta'), trim((string)$item->certNo . ' · ' . $item->certValidTo, ' ·'))
            ),
            span(setClass('label ' . ($days < 0 ? 'pm-health-red' : 'pm-health-yellow')), $daysLabel($days))
        );
    }
    $certPanel = div
    (
        setClass('pm-dash-panel is-list' . ($focusVal === 'certs' ? ' is-focus' : '')),
        setID('pm-focus-certs'),
        $listHeader($lang->jxboard->certs, (int)($board->certCount ?? 0), false, 'certs', $board->certExpiring),
        $certItems ? div(setClass('pm-dash-list'), $certItems) : $emptyBox(sprintf($lang->jxboard->emptyCerts, $certDays))
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
        $deptID = (string)($stat['id'] ?? '0');
        $deptRows[] = h::tr
        (
            h::td(a(set::href($boardLink($viewName, array('dept' => $deptID, 'focus' => ''))), $name)),
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
        $trendPanel,
        $loadPanel,
        div(setClass('pm-dash-grid is-lists is-four'), $overduePanel, $duePanel, $riskPanel, $certPanel)
    );
}
elseif($viewName == 'meeting' && !$isEmpty)
{
    $midGrid = div(setClass('pm-dash-grid is-lists is-four mt-4'), $riskPanel, $overduePanel, $duePanel, $certPanel);
}
elseif($viewName == 'dept' && !$isEmpty)
{
    $midGrid = div(setClass('pm-dash-grid is-lists is-single mt-4'), $riskPanel);
}

div
(
    setClass('pm-dashboard'),
    setID('pmDashboard-' . $viewName),
    $meetingActions,
    $cards,
    $pageEmpty,
    $isEmpty ? null : $midGrid,
    (!$isEmpty && $viewName == 'dept') ? $loadPanel : null,
    (!$isEmpty && ($viewName == 'portfolio' || $viewName == 'meeting' || $viewName == 'overview')) ? $portfolioPanel : null,
    (!$isEmpty && $viewName == 'dept') ? $deptFullPanel : null
);

render();
