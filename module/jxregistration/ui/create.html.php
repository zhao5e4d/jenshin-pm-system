<?php
declare(strict_types=1);
namespace zin;

$row = $row ?? null;
$isEdit = !empty($row);
$v = function($key, $default = '') use ($isEdit, $row)
{
    return $isEdit && isset($row->$key) ? $row->$key : $default;
};

formPanel
(
    set::title($isEdit ? $lang->jxregistration->edit : $lang->jxregistration->create),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxregistration->product), set::control('picker'), set::name('product'), set::items($products), set::value($v('product')), set::required(true)),
        formGroup(set::width('1/2'), set::label($lang->jxregistration->type), set::control('picker'), set::name('type'), set::items($lang->jxregistration->typeList), set::value($v('type', 'first')), set::required(true))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxregistration->name), set::name('name'), set::value($v('name')), set::required(true)),
        formGroup(set::width('1/2'), set::label($lang->jxregistration->code), set::name('code'), set::value($v('code')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxregistration->category), set::control('picker'), set::name('category'), set::items($lang->jxproduct->categoryList), set::value($v('category'))),
        formGroup(set::width('1/2'), set::label($lang->jxregistration->path), set::name('path'), set::value($v('path')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxregistration->acceptNo), set::name('acceptNo'), set::value($v('acceptNo'))),
        formGroup(set::width('1/2'), set::label($lang->jxregistration->certNo), set::name('certNo'), set::value($v('certNo')))
    ),
    formRow
    (
        formGroup(set::width('1/3'), set::label($lang->jxregistration->applyDate), set::control('date'), set::name('applyDate'), set::value($v('applyDate'))),
        formGroup(set::width('1/3'), set::label($lang->jxregistration->supplementDate), set::control('date'), set::name('supplementDate'), set::value($v('supplementDate'))),
        formGroup(set::width('1/3'), set::label($lang->jxregistration->certDate), set::control('date'), set::name('certDate'), set::value($v('certDate')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxregistration->certValidTo), set::control('date'), set::name('certValidTo'), set::value($v('certValidTo'))),
        formGroup(set::width('1/2'), set::label($lang->jxregistration->owner), set::control('picker'), set::name('owner'), set::items($users), set::value($v('owner')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxregistration->leadDept), set::control('picker'), set::name('leadDept'), set::items($depts), set::value($v('leadDept'))),
        formGroup(set::width('1/2'), set::label($lang->jxregistration->supportDepts), picker(set::name('supportDepts[]'), set::items($depts), set::multiple(true), set::value($isEdit ? $row->supportDepts : '')))
    ),
    formRow
    (
        formGroup(set::width('1/3'), set::label($lang->jxregistration->begin), set::control('date'), set::name('begin'), set::value($v('begin', date('Y-m-d'))), set::required(true)),
        formGroup(set::width('1/3'), set::label($lang->jxregistration->end), set::control('date'), set::name('end'), set::value($v('end'))),
        formGroup(set::width('1/3'), set::label($lang->jxregistration->budget), set::name('budget'), set::value($v('budget', 0)))
    ),
    formRow(formGroup(set::label($lang->jxregistration->desc), textarea(set::name('desc'), set::rows(3), set::value($v('desc')))))
);

render();
