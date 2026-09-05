<?php
/**
 * Keep the original product line on create/edit and the product list.
 */
global $lang, $config, $app;

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

/* 批量编辑：保留所属产品线，去掉测试负责人、发布负责人；可改型号 / 类别 / 证号 / 有效期。 */
unset($config->product->form->batchEdit['QD'], $config->product->form->batchEdit['RD'], $config->product->form->batchEdit['program']);
$app->loadLang('jxproduct');
$config->product->form->batchEdit['jxModel'] = array(
    'type' => 'string', 'control' => 'text', 'width' => '120px', 'required' => false, 'default' => '',
    'title' => $lang->jxproduct->model
);
$config->product->form->batchEdit['jxCategory'] = array(
    'type' => 'string', 'control' => 'select', 'width' => '120px', 'required' => false, 'default' => '',
    'options' => $lang->jxproduct->categoryList, 'title' => $lang->jxproduct->category
);
$config->product->form->batchEdit['jxCertNo'] = array(
    'type' => 'string', 'control' => 'text', 'width' => '160px', 'required' => false, 'default' => '',
    'title' => $lang->jxproduct->certNo
);
$config->product->form->batchEdit['jxCertValidTo'] = array(
    'type' => 'date', 'control' => 'date', 'width' => '128px', 'required' => false, 'default' => '',
    'title' => $lang->jxproduct->certValidTo
);
$config->product->custom->batchEditFields     = 'line,PO,status,type,acl,jxModel,jxCategory,jxCertNo,jxCertValidTo';
$config->product->list->customBatchEditFields = 'line,PO,status,type,acl,jxModel,jxCategory,jxCertNo,jxCertValidTo';

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
    $colKey = $col['name'] ?? 'productLine';
    if(isset($fieldList[$colKey]))
    {
        $fieldList[$colKey] = array_merge($fieldList[$colKey], $col);
        $fieldList[$colKey]['show'] = true;
        return $fieldList;
    }

    $afterKey = isset($fieldList['productLine']) ? 'productLine' : (isset($fieldList['code']) ? 'code' : 'name');
    if($colKey === 'productLine') $afterKey = isset($fieldList['code']) ? 'code' : 'name';

    $newList  = array();
    $inserted = false;
    foreach($fieldList as $key => $field)
    {
        $newList[$key] = $field;
        if($key === $afterKey)
        {
            $newList[$colKey] = $col;
            $inserted = true;
        }
    }
    if(!$inserted) $newList[$colKey] = $col;
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

$jxArchiveCols = array(
    'jxCertValidTo' => array(
        'name' => 'jxCertValidTo', 'title' => $lang->jxproduct->certValidTo, 'width' => 140, 'type' => 'html',
        'show' => true, 'sortType' => false, 'group' => 'g2', 'align' => 'left', 'border' => 'right'
    ),
    'jxCertNo' => array(
        'name' => 'jxCertNo', 'title' => $lang->jxproduct->certNo, 'width' => 140, 'type' => 'text',
        'show' => true, 'sortType' => false, 'group' => 'g2', 'align' => 'left'
    ),
    'jxCategory' => array(
        'name' => 'jxCategory', 'title' => $lang->jxproduct->category, 'width' => 88, 'type' => 'text',
        'show' => true, 'sortType' => false, 'group' => 'g2', 'align' => 'left'
    ),
    'jxModel' => array(
        'name' => 'jxModel', 'title' => $lang->jxproduct->model, 'width' => 110, 'type' => 'text',
        'show' => true, 'sortType' => false, 'group' => 'g2', 'align' => 'left'
    )
);
foreach($jxArchiveCols as $col)
{
    if(isset($config->product->all->dtable->fieldList)) $config->product->all->dtable->fieldList = $jxInsertAfter($config->product->all->dtable->fieldList, $col);
    if(isset($config->product->dtable->fieldList)) $config->product->dtable->fieldList = $jxInsertAfter($config->product->dtable->fieldList, $col);
}

/* 产品列表自定义列不展示 Bug、测试用例、发布相关字段。 */
$jxHideCols = array(
    'unresolvedBugs', 'totalBugs', 'bugFixedRate',
    'testCaseCoverage',
    'releases', 'latestReleaseDate', 'latestRelease',
    'jxUdi'
);
foreach($jxHideCols as $col)
{
    unset($config->product->all->dtable->fieldList[$col]);
    unset($config->product->dtable->fieldList[$col]);
}

/*
 * 产品名称不再落到 product-browse（需求列表）。按当前账号落到
 * 仍有权限的仪表盘 / 概况 / 关联项目，避免点进已隐藏的需求页。
 */
if(!function_exists('jxProductNameLink'))
{
    function jxProductNameLink(): array
    {
        $candidates = array(
            array('module' => 'product', 'method' => 'dashboard', 'params' => 'productID={id}'),
            array('module' => 'product', 'method' => 'view',      'params' => 'productID={id}'),
            array('module' => 'product', 'method' => 'project',   'params' => 'status=all&productID={id}'),
        );
        $fallback = array('module' => 'product', 'method' => 'all', 'params' => '');
        if(!class_exists('common')) return $fallback;
        foreach($candidates as $link)
        {
            if(common::hasPriv($link['module'], $link['method'])) return $link;
        }
        return $fallback;
    }
}

$jxNameLink = jxProductNameLink();
if(isset($config->product->all->dtable->fieldList['name']))
{
    $config->product->all->dtable->fieldList['name']['link'] = $jxNameLink;
}
if(isset($config->product->dtable->fieldList['name']))
{
    $config->product->dtable->fieldList['name']['link'] = $jxNameLink;
}
