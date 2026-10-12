<?php
/**
 * 阶段任务列表：名称列后面加一列「任务协同」，显示该任务下协同人的反馈进度。
 */
namespace zin;

global $lang, $app;

$app->loadLang('jxcollab');

onBeforeBuildNode(function($node) use ($lang, $app)
{
    if(!($node instanceof dtable) || $node->prop('id') !== 'tasks') return;

    $rows = $node->prop('data');
    if(!is_array($rows) || empty($rows)) return;

    $collect = function($rows) use (&$collect)
    {
        $list = array();
        foreach($rows as $row)
        {
            if(!is_object($row)) continue;
            $list[] = $row;
            if(!empty($row->children) && is_array($row->children)) $list = array_merge($list, $collect($row->children));
        }
        return $list;
    };

    $flatRows = $collect($rows);
    if(empty($flatRows)) return;

    $summary = $app->control->loadModel('jxcollab')->getSummaryByTasks(array_map(fn($row) => (int)$row->id, $flatRows));
    if(empty($summary)) return;

    foreach($flatRows as $row)
    {
        $stat = zget($summary, (int)$row->id, null);
        $row->jxcollab = empty($stat) ? '' : sprintf($lang->jxcollab->listSummary, $stat->settled, $stat->members);
    }

    $cols = $node->prop('cols');
    if(!is_array($cols)) return;

    /* 列必须是从 0 开始的数组。插一个字符串下标会让整列变成对象，表格渲染直接失败。 */
    $ordered  = array();
    $inserted = false;
    foreach($cols as $key => $col)
    {
        if(is_array($col) && ($col['name'] ?? '') === 'jxcollab') return;
        $ordered[] = $col;

        $colName = is_string($key) ? $key : '';
        if(is_array($col) && isset($col['name'])) $colName = $col['name'];
        if(!$inserted && $colName === 'name')
        {
            $ordered[] = array('name' => 'jxcollab', 'title' => $lang->jxcollab->common, 'width' => '110px', 'sortType' => false);
            $inserted  = true;
        }
    }
    if(!$inserted) $ordered[] = array('name' => 'jxcollab', 'title' => $lang->jxcollab->common, 'width' => '110px', 'sortType' => false);

    $node->setProp('cols', $ordered);
});
