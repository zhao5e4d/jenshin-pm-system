<?php
/**
 * 项目仪表盘「项目计划」长区块改为整行铺开；原先挤在右侧的短区块下移，避免重叠。
 */
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
