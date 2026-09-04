<?php
/**
 * 任务批量创建：去掉所属模块、相关需求及「只列零任务需求」。
 */
namespace zin;

onBeforeBuildNode(function($node)
{
    if(!($node instanceof formBatchPanel)) return;

    $custom = $node->prop('customFields');
    if(is_array($custom))
    {
        if(isset($custom['list']) && is_array($custom['list']))
        {
            unset($custom['list']['module'], $custom['list']['story'], $custom['list']['preview'], $custom['list']['copyStory']);
        }
        if(isset($custom['show']) && is_array($custom['show']))
        {
            $custom['show'] = array_values(array_diff($custom['show'], array('module', 'story', 'preview', 'copyStory')));
        }
        $node->setProp('customFields', $custom);
    }

    $removeNames = array('module', 'story', 'preview', 'copyStory', 'storyEstimate', 'storyDesc', 'storyPri');
    if(!empty($node->blocks['children']))
    {
        $kept = array();
        foreach($node->blocks['children'] as $child)
        {
            if($child instanceof formBatchItem && in_array((string)$child->prop('name'), $removeNames, true)) continue;
            $kept[] = $child;
        }
        $node->blocks['children'] = $kept;
    }

    if(empty($node->blocks['headingActions'])) return;

    $keptActions = array();
    foreach($node->blocks['headingActions'] as $child)
    {
        if($child instanceof checkbox && $child->id() === 'zeroTaskStory') continue;
        $keptActions[] = $child;
    }
    $node->blocks['headingActions'] = $keptActions;
});
