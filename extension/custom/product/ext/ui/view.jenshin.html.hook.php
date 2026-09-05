<?php
namespace zin;

global $app, $lang;

$product = data('product');
if(empty($product) || empty($product->id)) return;

$app->loadLang('jxproduct');
$jxcore  = $app->control->loadModel('jxcore');
$archive = $jxcore->getProductArchive((int)$product->id);
$other   = $jxcore->getProductOtherInfo((int)$product->id);

$jxOtherCell = function(string $className, string $label, $value)
{
    return div
    (
        setClass("w-1/4 item mb-3 {$className}"),
        span(setClass('text-gray'), $label),
        span(setClass('ml-2'), $value)
    );
};

$replaced = array();

$items = array(
    $lang->jxproduct->model        => $archive->model ?? '',
    $lang->jxproduct->category     => $archive->category ?? '',
    $lang->jxproduct->certNo       => $archive->certNo ?? '',
    $lang->jxproduct->certValidTo  => $archive->certValidTo ?? '',
    $lang->jxproduct->tenderCode   => $archive->tenderCode ?? '',
    $lang->jxproduct->manufacturer => $archive->manufacturer ?? '',
    $lang->jxproduct->specs        => $archive->specs ?? '',
    $lang->jxproduct->patents      => $archive->patents ?? ''
);

$cells = array();
foreach($items as $label => $value)
{
    $cells[] = div
    (
        setClass('w-1/4 item mb-3'),
        span(setClass('text-gray'), $label),
        span(setClass('ml-2'), $value !== '' && $value !== null ? $value : '-')
    );
}

$replaced[] = panel
(
    setClass('mb-4 jx-archive-box'),
    set::title($lang->jxproduct->common),
    div(setClass('flex flex-wrap pt-2'), $cells)
);

$replaced[] = panel
(
    setClass('otherInfoBox'),
    set::title($lang->product->otherInfo),
    div
    (
        setClass('flex flex-wrap'),
        $jxOtherCell('jx-other-plans', $lang->product->plans, $product->plans ?? 0),
        $jxOtherCell('jx-other-tasks', $lang->product->tasks, $other->tasks),
        $jxOtherCell('jx-other-unfinished', $lang->product->unfinishedTasks, $other->unfinishedTasks),
        $jxOtherCell('jx-other-projects', $lang->product->projects, $product->projects ?? 0),
        $jxOtherCell('jx-other-overdue', $lang->product->overdueTasks, $other->overdueTasks),
        $jxOtherCell('jx-other-docs', $lang->product->docs, $product->docs ?? 0),
        $jxOtherCell('jx-other-modules', $lang->product->modules, $other->modules),
        $jxOtherCell('jx-other-executions', $lang->product->executions, $product->executions ?? 0)
    )
);

query('.otherInfoBox')->replaceWith(...$replaced);
