<?php
namespace zin;

global $app, $lang;

$product = data('product');
if(empty($product) || empty($product->id)) return;

$app->loadLang('jxproduct');
$archive = $app->control->loadModel('jxcore')->getProductArchive((int)$product->id);
if(!$archive) return;

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

query('.otherInfoBox')->before
(
    panel
    (
        setClass('mb-4 jx-archive-box'),
        set::title($lang->jxproduct->common),
        div(setClass('flex flex-wrap pt-2'), $cells)
    )
);
