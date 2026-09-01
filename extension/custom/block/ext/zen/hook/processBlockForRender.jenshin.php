<?php
/**
 * 项目仪表盘「项目计划」长区块改为整行铺开；原先挤在右侧的短区块下移，避免重叠。
 * 已落库的「Bug统计」标题改成「任务统计」。
 * 关闭帮助时去掉「使用帮助」区块，避免已落库账号仍占位。
 */
if(empty($this->config->jenshin->enableHelp))
{
    foreach($blocks as $jxKey => $jxBlock)
    {
        if(($jxBlock->module ?? '') === 'guide' || ($jxBlock->code ?? '') === 'guide') unset($blocks[$jxKey]);
    }
}

foreach($blocks as $jxBlock)
{
    $jxCode  = $jxBlock->code ?? '';
    $jxTitle = $jxBlock->title ?? '';
    if(!in_array($jxCode, array('bugstatistic', 'singlebugstatistic'), true)) continue;
    $jxBlock->title = str_replace(
        array('Bug统计', 'Bug 统计', 'Bug Statistics', 'Bug Statistic'),
        array('任务统计', '任务统计', 'Task Statistics', 'Task Statistic'),
        $jxTitle
    );
}

$ganttBottom = 0;
foreach($blocks as $jxBlock)
{
    if(($jxBlock->code ?? '') !== 'waterfallgantt') continue;
    if((int)$jxBlock->width >= 3) continue;

    $jxBlock->width = 3;
    $jxBlock->left  = 0;
    $jxTop = (int)$jxBlock->top;
    if($jxTop < 0) $jxTop = 0;
    $ganttBottom = max($ganttBottom, $jxTop + (int)($jxBlock->height ?: 8));
}

if($ganttBottom > 0)
{
    foreach($blocks as $jxBlock)
    {
        if(($jxBlock->code ?? '') === 'waterfallgantt') continue;
        $jxTop = (int)$jxBlock->top;
        if($jxTop < 0) $jxTop = 0;
        if((int)$jxBlock->left > 0 && $jxTop < $ganttBottom) $jxBlock->top = $ganttBottom;
    }
}
