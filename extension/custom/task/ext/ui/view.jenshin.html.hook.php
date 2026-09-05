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

    if($node instanceof detail)
    {
        $tabs = $node->prop('tabs');
        if(empty($tabs) || !is_array($tabs)) return;

        $filtered = array();
        foreach($tabs as $key => $tab)
        {
            $item = $tab instanceof setting ? $tab->toArray() : (array)$tab;
            if(($item['control'] ?? '') === 'taskMiscInfo') continue;
            if(($item['title'] ?? '') === $lang->task->legendMisc) continue;
            $filtered[$key] = $tab;
        }
        $node->setProp('tabs', array_values($filtered));
        return;
    }

    if(!($node instanceof datalist)) return;
    $items = $node->prop('items');
    if(!is_array($items)) return;

    unset($items[$lang->task->fromBug], $items[$lang->task->module], $items[$lang->task->story]);
    $node->setProp('items', $items);
});
