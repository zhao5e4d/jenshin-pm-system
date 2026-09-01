<?php
/**
 * 项目仪表盘「项目计划」默认铺满整行（超长区块）。
 */
foreach(array('waterfallproject', 'waterfallplusproject', 'ipdproject') as $dash)
{
    if(empty($lang->block->default[$dash])) continue;
    foreach($lang->block->default[$dash] as $i => $block)
    {
        if(($block['code'] ?? '') === 'waterfallgantt') $lang->block->default[$dash][$i]['width'] = '3';
    }
}

$lang->block->themes = array();
$lang->block->themes['purple']     = '萱萱紫';
$lang->block->themes['default']    = '禅道蓝';
$lang->block->themes['blue']       = '青春蓝';
$lang->block->themes['green']      = '叶兰绿';
$lang->block->themes['red']        = '赤诚红';
$lang->block->themes['blackberry'] = '黑莓黑';

$lang->block->welcome->reviewByMe = '待我审批';
$lang->block->welcome->assignToMe = '需关注';

$lang->block->welcome->reviewList = array();
$lang->block->welcome->reviewList['reviewByMe'] = '待我审批数';

$lang->block->welcome->assignList = array();
$lang->block->welcome->assignList['task']         = '待办任务';
$lang->block->welcome->assignList['pendingStage'] = '待处理';
$lang->block->welcome->assignList['overdue']      = '逾期事项';
$lang->block->welcome->assignList['blocker']      = '阻塞事项';

$lang->block->summary->welcome      = '健忻已陪伴您%s： %s今日期待优秀的您来处理！';
$lang->block->summary->noWork       = '您暂无待处理事项，';
$lang->block->summary->fixBug       = '';
$lang->block->summary->currentAlert = '当前有%s项需关注，';

$lang->block->honorary = array();
$lang->block->honorary['task']   = '推进能手';
$lang->block->honorary['review'] = '审批先锋';

$lang->block->productoverview->activeBugCount       = '未完成任务数';
$lang->block->productoverview->finishedReleaseCount = '已完成项目数';
$lang->block->productoverview->releaseCount         = '今年完成项目';
$lang->block->productoverview->milestoneCount       = '未完成任务数';
$lang->block->monthlyprogress->bugTrendChart        = '任务新增和完成趋势图';
$lang->block->annualworkload->resolvedBugCount      = '完成任务数';
$lang->block->productlist->activatedBug             = '未完成任务';

$lang->block->qastatistic->fixBugRate    = '任务完成率';
$lang->block->qastatistic->bugStatusStat = '月度任务变化情况';
$lang->block->bugstatistic->effective    = '任务总数';
$lang->block->bugstatistic->fixed        = '已完成';
$lang->block->bugstatistic->activated    = '未完成';
$lang->block->tooltips['resolvedRate']   = "按{$lang->productCommon}统计的任务完成率 = 按{$lang->productCommon}统计的已完成任务数 / 按{$lang->productCommon}统计的未取消任务数";

if(!empty($lang->block->modules['product']->availableBlocks['bugstatistic']))
{
    $lang->block->modules['product']->availableBlocks['bugstatistic'] = "{$lang->productCommon}任务统计";
}
if(!empty($lang->block->modules['singleproduct']->availableBlocks['singlebugstatistic']))
{
    $lang->block->modules['singleproduct']->availableBlocks['singlebugstatistic'] = "{$lang->productCommon}任务统计";
}
foreach(array('product', 'singleproduct') as $jxDash)
{
    if(empty($lang->block->default[$jxDash])) continue;
    foreach($lang->block->default[$jxDash] as $jxIndex => $jxBlock)
    {
        $jxCode = $jxBlock['code'] ?? '';
        if($jxCode === 'bugstatistic' || $jxCode === 'singlebugstatistic')
        {
            $lang->block->default[$jxDash][$jxIndex]['title'] = str_replace('Bug统计', '任务统计', $jxBlock['title']);
        }
    }
}

$lang->block->teamachievement->createdTasks = '新增任务数量';
$lang->block->teamachievement->runCases     = '新增任务数量';

/* 关闭禅道「使用帮助」：新账号默认布局不再带该区块，添加区块列表也不再出现。 */
global $config;
if(empty($config->jenshin->enableHelp))
{
    unset($lang->block->moduleList['guide'], $lang->block->titleList['guide']);
    if(!empty($lang->block->default['full']['my']) && is_array($lang->block->default['full']['my']))
    {
        foreach($lang->block->default['full']['my'] as $jxIndex => $jxBlock)
        {
            if(($jxBlock['module'] ?? '') === 'guide' || ($jxBlock['code'] ?? '') === 'guide')
            {
                unset($lang->block->default['full']['my'][$jxIndex]);
            }
        }
    }
}

$lang->project->overdueTasks = '逾期任务';

