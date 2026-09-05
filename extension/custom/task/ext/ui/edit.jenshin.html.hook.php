<?php
namespace zin;

global $lang;

onBeforeBuildNode(function($node) use ($lang)
{
    if($node instanceof history)
    {
        $node->setProp('title', $lang->task->taskRecord);
        $node->setProp('commentBtn', $lang->task->addTaskRecord);
        return;
    }

    if($node instanceof item)
    {
        $name    = (string)$node->prop('name');
        $trClass = (string)$node->prop('trClass');
        if($name === $lang->task->module || $name === $lang->task->story || $trClass === 'moduleTR') $node->remove();
        return;
    }

    if($node instanceof formGridPanel || $node instanceof formPanel)
    {
        $fields = $node->prop('fields');
        if($fields && method_exists($fields, 'remove'))
        {
            $fields->remove('module,storyBox,story,storyEstimate,storyDesc,storyPri,testStoryBox');
            $node->setProp('fields', $fields);
        }
        return;
    }

    if($node instanceof field)
    {
        $name = (string)$node->prop('name');
        if(in_array($name, array('module', 'storyBox', 'story', 'storyEstimate', 'storyDesc', 'storyPri', 'testStoryBox'), true)) $node->remove();
        return;
    }

    if(!($node instanceof section)) return;
    if($node->prop('title') !== $lang->task->story) return;
    $node->remove();
});
