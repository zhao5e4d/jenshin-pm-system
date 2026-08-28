<?php
/**
 * Keep the original product line on create/edit and the product list.
 */
global $lang, $config;

if(empty($config->product->form->create['line']))
{
    $config->product->form->create['line'] = array('type' => 'int', 'control' => 'select', 'required' => false, 'default' => '', 'options' => array());
}
if(empty($config->product->form->edit['line']))
{
    $config->product->form->edit['line'] = array('type' => 'int', 'control' => 'select', 'required' => false, 'default' => 0, 'options' => array());
}
if(empty($config->product->form->batchEdit['line']))
{
    $config->product->form->batchEdit['line'] = array('type' => 'int', 'control' => 'select', 'width' => '200px', 'required' => false, 'default' => 0, 'options' => array());
}
/* Keep posted 所属产品线; do not let a missing field default-write 0. */
unset($config->product->form->batchEdit['line']['default']);

/* 批量编辑：保留所属产品线，去掉测试负责人、发布负责人。 */
unset($config->product->form->batchEdit['QD'], $config->product->form->batchEdit['RD'], $config->product->form->batchEdit['program']);
$config->product->custom->batchEditFields     = 'line,PO,status,type,acl';
$config->product->list->customBatchEditFields = 'line,PO,status,type,acl';

$jxProductLineCol = array(
    'name'     => 'productLine',
    'title'    => $lang->product->belongingLine,
    'width'    => 136,
    'type'     => 'format',
    'show'     => true,
    'sortType' => true,
    'group'    => 'g2',
    'border'   => 'right',
    'align'    => 'left'
);

$jxInsertAfter = function(array $fieldList, array $col): array
{
    if(isset($fieldList['productLine']))
    {
        $fieldList['productLine']['show']  = true;
        $fieldList['productLine']['title'] = $col['title'];
        return $fieldList;
    }

    $afterKey = isset($fieldList['code']) ? 'code' : 'name';
    $newList  = array();
    $inserted = false;
    foreach($fieldList as $key => $field)
    {
        $newList[$key] = $field;
        if($key === $afterKey)
        {
            $newList['productLine'] = $col;
            $inserted = true;
        }
    }
    if(!$inserted) $newList['productLine'] = $col;
    return $newList;
};

if(isset($config->product->all->dtable->fieldList))
{
    $config->product->all->dtable->fieldList = $jxInsertAfter($config->product->all->dtable->fieldList, $jxProductLineCol);
}
if(isset($config->product->dtable->fieldList))
{
    $config->product->dtable->fieldList = $jxInsertAfter($config->product->dtable->fieldList, $jxProductLineCol);
}

/* 产品列表自定义列不展示 Bug、测试用例、发布相关字段。 */
$jxHideCols = array(
    'unresolvedBugs', 'totalBugs', 'bugFixedRate',
    'testCaseCoverage',
    'releases', 'latestReleaseDate', 'latestRelease'
);
foreach($jxHideCols as $col)
{
    unset($config->product->all->dtable->fieldList[$col]);
    unset($config->product->dtable->fieldList[$col]);
}
