<?php
declare(strict_types=1);
namespace zin;

$product = $product ?? null;
$isEdit = !empty($product);

formPanel
(
    set::title($isEdit ? $lang->jxproduct->edit : $lang->jxproduct->create),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxproduct->name), set::name('name'), set::value($isEdit ? $product->name : ''), set::required(true)),
        formGroup(set::width('1/2'), set::label($lang->jxproduct->model), set::name('model'), set::value($isEdit ? $product->model : ''))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxproduct->category), set::control('picker'), set::name('category'), set::items($lang->jxproduct->categoryList), set::value($isEdit ? $product->category : '')),
        formGroup(set::width('1/2'), set::label($lang->jxproduct->line), set::name('line'), set::value($isEdit ? $product->line : ''))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxproduct->certNo), set::name('certNo'), set::value($isEdit ? $product->certNo : '')),
        formGroup(set::width('1/2'), set::label($lang->jxproduct->certValidTo), set::control('date'), set::name('certValidTo'), set::value($isEdit ? $product->certValidTo : ''))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxproduct->manufacturer), set::name('manufacturer'), set::value($isEdit ? $product->manufacturer : '')),
        formGroup(set::width('1/2'), set::label($lang->jxproduct->tenderCode), set::name('tenderCode'), set::value($isEdit ? $product->tenderCode : ''))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxproduct->specs), set::name('specs'), set::value($isEdit ? $product->specs : ''))
    ),
    formRow(formGroup(set::label($lang->jxproduct->patents), set::name('patents'), set::value($isEdit ? $product->patents : ''))),
    formRow(formGroup(set::label($lang->jxproduct->desc), textarea(set::name('desc'), set::rows(3), set::value($isEdit ? $product->desc : ''))))
);

render();
