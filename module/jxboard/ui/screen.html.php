<?php
declare(strict_types=1);
namespace zin;

$board      = $board;
$deptVal    = (string)$filters['dept'];
$productVal = (int)$filters['product'];
$healthVal  = (string)($filters['health'] ?? '');
$periodVal  = (string)($filters['period'] ?? 'week');
$beginVal   = (string)($filters['begin'] ?? '');
$endVal     = (string)($filters['end'] ?? '');
$isEmpty    = !empty($board->isEmpty);

$filterQuery = function($overrides = array()) use ($filters)
{
    $f = array_merge($filters, $overrides);
    $begin = preg_replace('/-/', '', (string)($f['begin'] ?? ''));
    $end   = preg_replace('/-/', '', (string)($f['end'] ?? ''));
    return sprintf(
        'dept=%s&product=%s&status=%s&health=%s&period=%s&begin=%s&end=%s&focus=',
        $f['dept'] ?? '',
        (int)($f['product'] ?? 0),
        $f['status'] ?? '',
        $f['health'] ?? '',
        $f['period'] ?? 'week',
        $begin,
        $end
    );
};

$clearLink = createLink('jxboard', 'screen', $filterQuery(array(
    'dept' => '', 'product' => 0, 'status' => '', 'health' => '',
    'period' => 'week', 'begin' => '', 'end' => ''
)));

$axisText = array('color' => '#8aa0b8', 'fontSize' => 11);
$tooltip  = array(
    'backgroundColor' => 'rgba(7,13,26,0.92)',
    'borderColor'     => 'rgba(34,211,238,0.35)',
    'textStyle'       => array('color' => '#d7e6f7', 'fontSize' => 12)
);
$splitLine = array('lineStyle' => array('color' => 'rgba(34,211,238,0.08)'));
$legend    = array('textStyle' => array('color' => '#9fb4c9', 'fontSize' => 11), 'top' => 4, 'right' => 8);

$panelNo = 0;
$panelTitle = function($title) use (&$panelNo)
{
    $panelNo++;
    return div
    (
        setClass('jx-screen-panel-title'),
        span(setClass('jx-screen-panel-no'), str_pad((string)$panelNo, 2, '0', STR_PAD_LEFT)),
        span($title)
    );
};

$emptyPanel = function($title, $text, $link = '', $linkText = '') use ($panelTitle)
{
    return div
    (
        setClass('jx-screen-panel'),
        $panelTitle($title),
        div
        (
            setClass('jx-screen-empty'),
            div(setClass('jx-screen-empty-text'), $text),
            $link ? a(setClass('jx-screen-link'), set::href($link), $linkText) : null
        )
    );
};

$chartPanel = function($title, $chart) use ($panelTitle)
{
    return div
    (
        setClass('jx-screen-panel'),
        $panelTitle($title),
        $chart
    );
};

$kpiCard = function($label, $value, $tone = '', $index = 1)
{
    return div
    (
        setClass('jx-screen-kpi' . ($tone !== '' ? " is-{$tone}" : '')),
        div(setClass('jx-screen-kpi-index'), str_pad((string)$index, 2, '0', STR_PAD_LEFT)),
        div(setClass('jx-screen-kpi-label'), $label),
        div(setClass('jx-screen-kpi-value'), set('data-count', (string)$value), $value),
        div(setClass('jx-screen-kpi-rail'), '')
    );
};

$glowBar = function($from, $to)
{
    return array(
        'color' => array(
            'type' => 'linear',
            'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
            'colorStops' => array(
                array('offset' => 0, 'color' => $from),
                array('offset' => 1, 'color' => $to)
            )
        ),
        'shadowBlur'  => 12,
        'shadowColor' => $from,
        'borderRadius'=> array(4, 4, 0, 0)
    );
};

$deptRows = array_values($board->byDept ?? array());
usort($deptRows, function($a, $b)
{
    return ((int)$b['count'] <=> (int)$a['count']);
});
if(count($deptRows) > 10) $deptRows = array_slice($deptRows, 0, 10);

$deptNames = $deptProjects = $deptExecs = $deptProducts = $deptOverdue = array();
$deptWait  = $deptDoing = $deptDone = $deptConsumed = array();
foreach($deptRows as $stat)
{
    $deptNames[]    = $stat['name'];
    $deptProjects[] = (int)$stat['count'];
    $deptExecs[]    = (int)($stat['execTotal'] ?? 0);
    $deptProducts[] = (int)($stat['products'] ?? 0);
    $deptOverdue[]  = (int)($stat['overdue'] ?? 0);
    $deptWait[]     = (int)($stat['execWait'] ?? 0);
    $deptDoing[]    = (int)($stat['execDoing'] ?? 0);
    $deptDone[]     = (int)($stat['execDone'] ?? 0);
    $deptConsumed[] = round((float)($stat['consumed'] ?? 0), 1);
}

$productRows = array_values($board->byProduct ?? array());
usort($productRows, function($a, $b)
{
    return ((int)$b['count'] <=> (int)$a['count']);
});
if(count($productRows) > 8) $productRows = array_slice($productRows, 0, 8);
$productPie = array();
foreach($productRows as $row)
{
    if((int)$row['count'] <= 0) continue;
    $productPie[] = array('name' => $row['name'], 'value' => (int)$row['count']);
}

$healthPie = array();
foreach(array('green' => $lang->jxboard->green, 'yellow' => $lang->jxboard->yellow, 'red' => $lang->jxboard->red) as $key => $label)
{
    $healthPie[] = array('name' => $label, 'value' => (int)($board->byHealth[$key] ?? 0));
}

$trendLabels = $trendTasks = $trendHours = array();
if(!empty($board->trends->buckets))
{
    foreach($board->trends->buckets as $bucket)
    {
        $trendLabels[] = $bucket['label'];
        $trendTasks[]  = (int)$bucket['tasks'];
        $trendHours[]  = round((float)$bucket['hours'], 1);
    }
}

$doneRate = max(0, min(1, ((float)($board->taskDoneRate ?? 0)) / 100));
$riskList = array_slice(array_values($board->riskProjects ?? array()), 0, 5);

$compareChart = echarts
(
    set::width('100%'),
    set::height(280),
    set::responsive(true),
    set::animationDuration(1200),
    set::tooltip(array_merge($tooltip, array('trigger' => 'axis'))),
    set::legend(array_merge($legend, array('data' => array($lang->jxboard->colCount, $lang->jxboard->executions, $lang->jxboard->colProducts, $lang->jxboard->overdueTasks)))),
    set::grid(array('left' => 8, 'right' => 8, 'top' => 36, 'bottom' => 8, 'containLabel' => true)),
    set::xAxis(array('type' => 'category', 'data' => $deptNames, 'axisLabel' => array_merge($axisText, array('rotate' => count($deptNames) > 5 ? 24 : 0)), 'axisLine' => array('lineStyle' => array('color' => 'rgba(34,211,238,0.25)')))),
    set::yAxis(array('type' => 'value', 'splitLine' => $splitLine, 'axisLabel' => $axisText)),
    set::animationEasing('cubicOut'),
    set::series(array(
        array('name' => $lang->jxboard->colCount, 'type' => 'bar', 'barMaxWidth' => 14, 'itemStyle' => $glowBar('#67e8f9', '#0891b2'), 'data' => $deptProjects),
        array('name' => $lang->jxboard->executions, 'type' => 'bar', 'barMaxWidth' => 14, 'itemStyle' => $glowBar('#c4b5fd', '#7c3aed'), 'data' => $deptExecs),
        array('name' => $lang->jxboard->colProducts, 'type' => 'bar', 'barMaxWidth' => 14, 'itemStyle' => $glowBar('#6ee7b7', '#059669'), 'data' => $deptProducts),
        array('name' => $lang->jxboard->overdueTasks, 'type' => 'bar', 'barMaxWidth' => 14, 'itemStyle' => $glowBar('#fda4af', '#e11d48'), 'data' => $deptOverdue)
    ))
);

$healthChart = echarts
(
    set::width('100%'),
    set::height(260),
    set::responsive(true),
    set::animationDuration(1100),
    set::tooltip(array_merge($tooltip, array('trigger' => 'item'))),
    set::legend(array_merge($legend, array('bottom' => 0, 'top' => 'auto', 'right' => 'auto'))),
    set::color(array('#34d399', '#f59e0b', '#fb7185')),
    set::series(array(array(
        'type'      => 'pie',
        'radius'    => array('44%', '70%'),
        'center'    => array('50%', '46%'),
        'itemStyle' => array('borderColor' => '#071018', 'borderWidth' => 3, 'shadowBlur' => 18, 'shadowColor' => 'rgba(52,211,153,0.25)'),
        'label'     => array('color' => '#d7e6f7', 'fontSize' => 11),
        'emphasis'  => array('itemStyle' => array('shadowBlur' => 24, 'shadowColor' => 'rgba(34,211,238,0.45)')),
        'data'      => $healthPie
    )))
);

$productChart = echarts
(
    set::width('100%'),
    set::height(260),
    set::responsive(true),
    set::animationDuration(1100),
    set::tooltip(array_merge($tooltip, array('trigger' => 'item'))),
    set::color(array('#22d3ee', '#38bdf8', '#a78bfa', '#f59e0b', '#34d399', '#fb7185', '#67e8f9', '#c084fc')),
    set::series(array(array(
        'type'      => 'pie',
        'roseType'  => 'radius',
        'radius'    => array('18%', '68%'),
        'center'    => array('50%', '50%'),
        'itemStyle' => array('borderColor' => '#071018', 'borderWidth' => 2, 'shadowBlur' => 16, 'shadowColor' => 'rgba(34,211,238,0.28)'),
        'label'     => array('color' => '#d7e6f7', 'fontSize' => 11),
        'emphasis'  => array('scale' => true, 'itemStyle' => array('shadowBlur' => 22)),
        'data'      => $productPie
    )))
);

$execChart = echarts
(
    set::width('100%'),
    set::height(280),
    set::responsive(true),
    set::animationDuration(1200),
    set::tooltip(array_merge($tooltip, array('trigger' => 'axis'))),
    set::legend(array_merge($legend, array('data' => array($lang->jxboard->funnelList['wait'], $lang->jxboard->funnelList['doing'], $lang->jxboard->funnelList['done'])))),
    set::grid(array('left' => 8, 'right' => 8, 'top' => 36, 'bottom' => 8, 'containLabel' => true)),
    set::xAxis(array('type' => 'category', 'data' => $deptNames, 'axisLabel' => array_merge($axisText, array('rotate' => count($deptNames) > 5 ? 24 : 0)), 'axisLine' => array('lineStyle' => array('color' => 'rgba(34,211,238,0.25)')))),
    set::yAxis(array('type' => 'value', 'splitLine' => $splitLine, 'axisLabel' => $axisText)),
    set::series(array(
        array('name' => $lang->jxboard->funnelList['wait'], 'type' => 'bar', 'stack' => 'exec', 'barMaxWidth' => 22, 'itemStyle' => $glowBar('#94a3b8', '#334155'), 'data' => $deptWait),
        array('name' => $lang->jxboard->funnelList['doing'], 'type' => 'bar', 'stack' => 'exec', 'barMaxWidth' => 22, 'itemStyle' => $glowBar('#67e8f9', '#0e7490'), 'data' => $deptDoing),
        array('name' => $lang->jxboard->funnelList['done'], 'type' => 'bar', 'stack' => 'exec', 'barMaxWidth' => 22, 'itemStyle' => $glowBar('#6ee7b7', '#047857'), 'data' => $deptDone)
    ))
);

$trendChart = echarts
(
    set::width('100%'),
    set::height(280),
    set::responsive(true),
    set::animationDuration(1400),
    set::tooltip(array_merge($tooltip, array('trigger' => 'axis'))),
    set::legend(array_merge($legend, array('data' => array($lang->jxboard->trendTasks, $lang->jxboard->trendHours)))),
    set::grid(array('left' => 8, 'right' => 12, 'top' => 36, 'bottom' => 8, 'containLabel' => true)),
    set::xAxis(array('type' => 'category', 'boundaryGap' => false, 'data' => $trendLabels, 'axisLabel' => $axisText, 'axisLine' => array('lineStyle' => array('color' => 'rgba(34,211,238,0.25)')))),
    set::yAxis(array(
        array('type' => 'value', 'name' => $lang->jxboard->trendTasks, 'splitLine' => $splitLine, 'axisLabel' => $axisText, 'nameTextStyle' => $axisText),
        array('type' => 'value', 'name' => $lang->jxboard->trendHours, 'splitLine' => array('show' => false), 'axisLabel' => $axisText, 'nameTextStyle' => $axisText)
    )),
    set::series(array(
        array(
            'name' => $lang->jxboard->trendTasks,
            'type' => 'line',
            'smooth' => true,
            'symbol' => 'circle',
            'symbolSize' => 8,
            'itemStyle' => array('color' => '#67e8f9', 'shadowBlur' => 10, 'shadowColor' => '#22d3ee'),
            'lineStyle' => array('width' => 2.5, 'shadowBlur' => 16, 'shadowColor' => 'rgba(34,211,238,0.55)'),
            'areaStyle' => array('color' => array('type' => 'linear', 'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1, 'colorStops' => array(
                array('offset' => 0, 'color' => 'rgba(34,211,238,0.42)'),
                array('offset' => 1, 'color' => 'rgba(34,211,238,0.02)')
            ))),
            'data' => $trendTasks
        ),
        array(
            'name' => $lang->jxboard->trendHours,
            'type' => 'line',
            'smooth' => true,
            'yAxisIndex' => 1,
            'symbol' => 'circle',
            'symbolSize' => 8,
            'itemStyle' => array('color' => '#fbbf24', 'shadowBlur' => 10, 'shadowColor' => '#f59e0b'),
            'lineStyle' => array('width' => 2.5, 'shadowBlur' => 16, 'shadowColor' => 'rgba(245,158,11,0.5)'),
            'areaStyle' => array('color' => array('type' => 'linear', 'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1, 'colorStops' => array(
                array('offset' => 0, 'color' => 'rgba(245,158,11,0.32)'),
                array('offset' => 1, 'color' => 'rgba(245,158,11,0.02)')
            ))),
            'data' => $trendHours
        )
    ))
);

$loadChart = echarts
(
    set::width('100%'),
    set::height(230),
    set::responsive(true),
    set::animationDuration(1200),
    set::tooltip(array_merge($tooltip, array('trigger' => 'axis'))),
    set::legend(array_merge($legend, array('data' => array($lang->jxboard->consumed, $lang->jxboard->overdueTasks)))),
    set::grid(array('left' => 8, 'right' => 12, 'top' => 36, 'bottom' => 8, 'containLabel' => true)),
    set::xAxis(array('type' => 'category', 'data' => $deptNames, 'axisLabel' => array_merge($axisText, array('rotate' => count($deptNames) > 5 ? 24 : 0)), 'axisLine' => array('lineStyle' => array('color' => 'rgba(34,211,238,0.25)')))),
    set::yAxis(array(
        array('type' => 'value', 'splitLine' => $splitLine, 'axisLabel' => $axisText),
        array('type' => 'value', 'splitLine' => array('show' => false), 'axisLabel' => $axisText)
    )),
    set::series(array(
        array('name' => $lang->jxboard->consumed, 'type' => 'bar', 'barMaxWidth' => 18, 'itemStyle' => $glowBar('#67e8f9', '#0e7490'), 'data' => $deptConsumed),
        array('name' => $lang->jxboard->overdueTasks, 'type' => 'line', 'yAxisIndex' => 1, 'smooth' => true, 'symbolSize' => 8, 'itemStyle' => array('color' => '#fb7185', 'shadowBlur' => 10, 'shadowColor' => '#fb7185'), 'lineStyle' => array('width' => 2.5, 'shadowBlur' => 14, 'shadowColor' => 'rgba(251,113,133,0.5)'), 'data' => $deptOverdue)
    ))
);

$liquidChart = echarts
(
    set::width('100%'),
    set::height(148),
    set::responsive(true),
    set::exts('liquidfill'),
    set::series(array(array(
        'type'            => 'liquidFill',
        'data'            => array($doneRate, max(0, $doneRate * 0.72), max(0, $doneRate * 0.48)),
        'color'           => array('#22d3ee', '#38bdf8', '#67e8f9'),
        'radius'          => '78%',
        'backgroundStyle' => array('color' => 'rgba(8,20,40,0.65)'),
        'outline'         => array(
            'show'           => true,
            'borderDistance' => 4,
            'itemStyle'      => array('borderColor' => '#22d3ee', 'borderWidth' => 2, 'shadowBlur' => 14, 'shadowColor' => 'rgba(34,211,238,0.5)')
        ),
        'label'           => array('fontSize' => 22, 'color' => '#e8f6ff', 'fontWeight' => 700, 'textShadowColor' => 'rgba(34,211,238,0.6)', 'textShadowBlur' => 12),
        'waveAnimation'   => true
    )))
);

$riskItems = array();
foreach($riskList as $item)
{
    $tone = $item->health === 'red' ? 'red' : 'yellow';
    $riskItems[] = a
    (
        setClass('jx-screen-risk'),
        set::href(createLink('project', 'view', "projectID={$item->id}")),
        span(setClass("jx-screen-dot is-{$tone}")),
        span(setClass('jx-screen-risk-name'), $item->name),
        span(setClass('jx-screen-risk-meta'), $item->deptName),
        span(setClass('jx-screen-risk-meta'), $item->blocker !== '' ? $item->blocker : zget($lang->jxboard->healthList, $item->health, $item->health))
    );
}

$pageEmpty = null;
if($isEmpty)
{
    $title = !empty($board->filteredEmpty) ? $lang->jxboard->emptyFilterTitle : $lang->jxboard->emptyTitle;
    $desc  = !empty($board->filteredEmpty) ? $lang->jxboard->emptyFilterDesc : $lang->jxboard->emptyDesc;
    $pageEmpty = div
    (
        setClass('jx-screen-hero-empty'),
        div(setClass('jx-screen-empty-title'), $title),
        div(setClass('jx-screen-empty-text'), $desc),
        !empty($board->filteredEmpty) ? a(setClass('jx-screen-link'), set::href($clearLink), $lang->jxboard->clearFilter) : null
    );
}

$hasDeptCharts = !$isEmpty && $deptNames;
$hasProduct    = !$isEmpty && $productPie;
$hasTrend      = !$isEmpty && !empty($board->trends->hasData);

div
(
    setClass('jx-screen'),
    setID('jxMeetingScreen'),
    div(setClass('jx-screen-frame'), ''),
    div(setClass('jx-screen-fx'), ''),
    div
    (
        setClass('jx-screen-head'),
        div
        (
            setClass('jx-screen-brand'),
            div(setClass('jx-screen-logo'), span(setClass('jx-screen-logo-core'), '')),
            div
            (
                setClass('jx-screen-titles'),
                div
                (
                    setClass('jx-screen-kicker-row'),
                    div(setClass('jx-screen-kicker'), $lang->jxboard->common),
                    span(setClass('jx-screen-live'), span(setClass('jx-screen-live-dot'), ''), $lang->jxboard->screenLive)
                ),
                div(setClass('jx-screen-title'), $lang->jxboard->screenTitle)
            )
        ),
        div
        (
            setClass('jx-screen-clock'),
            setID('jxScreenClock'),
            span(setClass('jx-screen-clock-date'), date('Y.m.d')),
            span(setClass('jx-screen-clock-time'), date('H:i:s'))
        ),
        div
        (
            setClass('jx-screen-filters'),
            picker(set::name('dept'), set::items(array('' => $lang->jxboard->all, '0' => $lang->jxboard->unassigned) + $depts), set::value($deptVal), set::placeholder($lang->jxboard->deptName), on::change('window.jxScreenChange')),
            picker(set::name('product'), set::items(array('0' => $lang->jxboard->allProducts) + $products), set::value((string)$productVal), set::placeholder($lang->jxboard->product), on::change('window.jxScreenChange')),
            picker(set::name('health'), set::items($healthItems), set::value($healthVal), set::placeholder($lang->jxboard->health), on::change('window.jxScreenChange')),
            picker(set::name('period'), set::items($periodItems), set::value($periodVal), set::placeholder($lang->jxboard->period), on::change('window.jxScreenChange')),
            div
            (
                setClass('jx-screen-dates' . ($periodVal === 'custom' ? '' : ' hidden')),
                h::input(setClass('form-control jx-screen-date'), set(array('type' => 'date', 'name' => 'begin', 'value' => $beginVal)), on::change('window.jxScreenChange')),
                h::input(setClass('form-control jx-screen-date'), set(array('type' => 'date', 'name' => 'end', 'value' => $endVal)), on::change('window.jxScreenChange'))
            ),
            button(setClass('jx-screen-full'), set::type('button'), $lang->jxboard->fullscreen)
        )
    ),
    $isEmpty ? $pageEmpty : div
    (
        setClass('jx-screen-body'),
        div
        (
            setClass('jx-screen-kpis'),
            $kpiCard($lang->jxboard->total, (int)$board->total, '', 1),
            $kpiCard($lang->jxboard->green, (int)($board->byHealth['green'] ?? 0), 'green', 2),
            $kpiCard($lang->jxboard->yellow, (int)($board->byHealth['yellow'] ?? 0), 'yellow', 3),
            $kpiCard($lang->jxboard->red, (int)($board->byHealth['red'] ?? 0), 'red', 4),
            $kpiCard($lang->jxboard->executions, (int)($board->executionTotal ?? 0), '', 5),
            $kpiCard($lang->jxboard->overdueTasks, (int)$board->overdueTaskCount, 'red', 6),
            $kpiCard($lang->jxboard->dueStages, (int)$board->dueExecutionCount, 'yellow', 7),
            div
            (
                setClass('jx-screen-liquid'),
                div(setClass('jx-screen-kpi-label'), $lang->jxboard->taskDoneRate),
                $liquidChart
            )
        ),
        div
        (
            setClass('jx-screen-grid'),
            $hasDeptCharts ? $chartPanel($lang->jxboard->deptCompare, $compareChart) : $emptyPanel($lang->jxboard->deptCompare, $lang->jxboard->emptyLoad),
            div
            (
                setClass('jx-screen-panel'),
                $panelTitle($lang->jxboard->healthAndProduct),
                div
                (
                    setClass('jx-screen-split'),
                    $healthChart,
                    $hasProduct ? $productChart : div(setClass('jx-screen-empty'), div(setClass('jx-screen-empty-text'), $lang->jxboard->emptyProduct))
                )
            ),
            $hasDeptCharts ? $chartPanel($lang->jxboard->execProgress, $execChart) : $emptyPanel($lang->jxboard->execProgress, $lang->jxboard->emptyFunnel),
            $hasTrend ? $chartPanel($lang->jxboard->trend, $trendChart) : $emptyPanel($lang->jxboard->trend, $lang->jxboard->emptyTrend)
        ),
        div
        (
            setClass('jx-screen-foot'),
            $hasDeptCharts ? $chartPanel($lang->jxboard->deptLoadDual, $loadChart) : $emptyPanel($lang->jxboard->deptLoadDual, $lang->jxboard->emptyLoad),
            div
            (
                setClass('jx-screen-panel is-list'),
                $panelTitle($lang->jxboard->riskProjects),
                $riskItems
                    ? div(setClass('jx-screen-risks'), $riskItems)
                    : div(setClass('jx-screen-empty'), div(setClass('jx-screen-empty-text'), $lang->jxboard->emptyRisks))
            )
        )
    )
);

render();
