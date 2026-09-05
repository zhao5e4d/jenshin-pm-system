<?php
/**
 * 单任务创建：去掉相关需求及测试需求勾选。
 */
namespace zin;

onBeforeBuildNode(function($node)
{
    if($node instanceof formGridPanel || $node instanceof formPanel)
    {
        $fields = $node->prop('fields');
        if($fields && method_exists($fields, 'remove'))
        {
            $fields->remove('storyBox,story,storyEstimate,storyDesc,storyPri,testStoryBox');
            $node->setProp('fields', $fields);
        }
        return;
    }

    if(!($node instanceof field)) return;
    $name = (string)$node->prop('name');
    if(in_array($name, array('storyBox', 'story', 'storyEstimate', 'storyDesc', 'storyPri', 'testStoryBox'), true)) $node->remove();
});
