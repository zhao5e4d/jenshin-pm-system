<?php
declare(strict_types=1);
/**
 * Batch edit products: keep 所属产品线, drop 测试负责人 / 发布负责人.
 */
namespace zin;

$jxSkipBatchFields = array('QD' => true, 'RD' => true, 'program' => true, 'jxUdi' => true);
$lineOptions       = (isset($fields['line']['options']) && is_array($fields['line']['options'])) ? $fields['line']['options'] : array();
if(empty($lineOptions))
{
    global $app;
    $lineOptions = $app->control->loadModel('product')->getLinePairs(0, false);
}
if(!isset($fields['line']))
{
    $fields['line'] = array('title' => $lang->product->belongingLine, 'control' => 'select', 'width' => '200px', 'required' => false, 'options' => $lineOptions);
}
else
{
    $fields['line']['options'] = $lineOptions;
}

$app->loadLang('jxproduct');
if(!isset($fields['jxModel']))
{
    $fields['jxModel'] = array('title' => $lang->jxproduct->model, 'control' => 'text', 'width' => '120px', 'required' => false, 'options' => array());
}
if(!isset($fields['jxCategory']))
{
    $fields['jxCategory'] = array('title' => $lang->jxproduct->category, 'control' => 'select', 'width' => '120px', 'required' => false, 'options' => $lang->jxproduct->categoryList);
}
if(!isset($fields['jxCertNo']))
{
    $fields['jxCertNo'] = array('title' => $lang->jxproduct->certNo, 'control' => 'text', 'width' => '160px', 'required' => false, 'options' => array());
}
if(!isset($fields['jxCertValidTo']))
{
    $fields['jxCertValidTo'] = array('title' => $lang->jxproduct->certValidTo, 'control' => 'date', 'width' => '128px', 'required' => false, 'options' => array());
}

if(!is_array($lines)) $lines = array();
if(empty($lines[0]) && $lineOptions) $lines[0] = $lineOptions;
if(!empty($products))
{
    foreach($products as $product)
    {
        $programID = isset($product->program) ? (int)$product->program : 0;
        if(empty($lines[$programID]) && $lineOptions) $lines[$programID] = $lineOptions;
        if(isset($product->line)) $product->line = $product->line ? (string)$product->line : '';
        if(!empty($product->jxCertValidTo) && ($product->jxCertValidTo === '0000-00-00' || strpos((string)$product->jxCertValidTo, '<') !== false)) $product->jxCertValidTo = '';
    }
}

jsVar('lines', $lines);

$items = array();
$items['productIdList'] = array('name' => 'productIdList', 'label' => '', 'control' => 'hidden', 'hidden' => true);
$items['id']            = array('name' => 'id', 'label' => $lang->idAB, 'control' => 'index', 'width' => '60px');
foreach($fields as $fieldName => $field)
{
    if(isset($jxSkipBatchFields[$fieldName])) continue;
    $items[$fieldName] = array('name' => $fieldName, 'label' => $field['title'], 'control' => $field['control'], 'width' => $field['width'], 'required' => $field['required'], 'items' => zget($field, 'options', array()));
    if($items[$fieldName]['control'] == 'select') $items[$fieldName]['control'] = 'picker';
}
if(isset($items['line']))
{
    $items['line']['label'] = $lang->product->belongingLine;
    $lineItems = array();
    foreach((array)$items['line']['items'] as $lineID => $lineName) $lineItems[] = array('text' => $lineName, 'value' => (string)$lineID);
    $items['line']['items'] = $lineItems;
}
if(isset($items['acl']))
{
    $items['acl']['control'] = array('control' => $items['acl']['control'], 'inline' => true);
    if($app->getClientLang() == 'en') $items['acl']['width'] = '150px';
}

unset($customFields['QD'], $customFields['RD'], $customFields['program']);
$customFields = array(
    'line'          => $lang->product->belongingLine,
    'jxModel'       => $lang->jxproduct->model,
    'jxCategory'    => $lang->jxproduct->category,
    'jxCertNo'      => $lang->jxproduct->certNo,
    'jxCertValidTo' => $lang->jxproduct->certValidTo
) + $customFields;

$showList = array();
foreach(explode(',', (string)$showFields) as $fieldName)
{
    $fieldName = trim($fieldName);
    if($fieldName === '' || isset($jxSkipBatchFields[$fieldName])) continue;
    $showList[] = $fieldName;
}
if(!in_array('line', $showList, true)) array_unshift($showList, 'line');
foreach(array('jxModel', 'jxCategory', 'jxCertNo', 'jxCertValidTo') as $jxField)
{
    if(!in_array($jxField, $showList, true)) $showList[] = $jxField;
}

formBatchPanel
(
    on::change('[data-name="program"]', 'loadProductLines'),
    set::title($lang->product->batchEdit),
    set::mode('edit'),
    set::customFields(array('list' => $customFields, 'show' => $showList, 'key' => 'batchEditFields')),
    set::items($items),
    set::data(array_values($products)),
    set::onRenderRow(jsRaw('renderRowData'))
);

render();
