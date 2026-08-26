<?php
declare(strict_types=1);
namespace zin;
$row = $row ?? null;
$isEdit = !empty($row);
$v = function($key, $default = '') use ($isEdit, $row) { return $isEdit && isset($row->$key) ? $row->$key : $default; };
formPanel
(
    set::title($isEdit ? $lang->jxadmission->edit : $lang->jxadmission->create),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxadmission->product), set::control('picker'), set::name('product'), set::items($products), set::value($v('product')), set::required(true)),
        formGroup(set::width('1/2'), set::label($lang->jxadmission->hospital), set::control('picker'), set::name('hospital'), set::items($hospitals), set::value($v('hospital')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxadmission->name), set::name('name'), set::value($v('name')), set::required(true)),
        formGroup(set::width('1/2'), set::label($lang->jxadmission->code), set::name('code'), set::value($v('code')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxadmission->path), set::name('path'), set::value($v('path'))),
        formGroup(set::width('1/2'), set::label($lang->jxadmission->department), set::name('department'), set::value($v('department')))
    ),
    formRow
    (
        formGroup(set::width('1/3'), set::label($lang->jxadmission->pharmacyDate), set::control('date'), set::name('pharmacyDate'), set::value($v('pharmacyDate'))),
        formGroup(set::width('1/3'), set::label($lang->jxadmission->firstOrderDate), set::control('date'), set::name('firstOrderDate'), set::value($v('firstOrderDate'))),
        formGroup(set::width('1/3'), set::label($lang->jxadmission->volume), set::name('volume'), set::value($v('volume', 0)))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxadmission->repurchase), set::control('picker'), set::name('repurchase'), set::items($lang->jxadmission->repurchaseList), set::value($v('repurchase'))),
        formGroup(set::width('1/2'), set::label($lang->jxadmission->owner), set::control('picker'), set::name('owner'), set::items($users), set::value($v('owner')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxadmission->leadDept), set::control('picker'), set::name('leadDept'), set::items($depts), set::value($v('leadDept'))),
        formGroup(set::width('1/2'), set::label($lang->jxadmission->supportDepts), picker(set::name('supportDepts[]'), set::items($depts), set::multiple(true), set::value($isEdit ? $row->supportDepts : '')))
    ),
    formRow
    (
        formGroup(set::width('1/3'), set::label($lang->jxadmission->begin), set::control('date'), set::name('begin'), set::value($v('begin', date('Y-m-d'))), set::required(true)),
        formGroup(set::width('1/3'), set::label($lang->jxadmission->end), set::control('date'), set::name('end'), set::value($v('end'))),
        formGroup(set::width('1/3'), set::label($lang->jxadmission->budget), set::name('budget'), set::value($v('budget', 0)))
    ),
    formRow(formGroup(set::label($lang->jxadmission->desc), textarea(set::name('desc'), set::rows(3), set::value($v('desc')))))
);
render();
