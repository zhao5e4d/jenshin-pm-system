<?php
declare(strict_types=1);
namespace zin;
$row = $row ?? null;
$isEdit = !empty($row);
$v = function($key, $default = '') use ($isEdit, $row) { return $isEdit && isset($row->$key) ? $row->$key : $default; };

formPanel
(
    set::title($isEdit ? $lang->jxmarketaccess->edit : $lang->jxmarketaccess->create),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->product), set::control('picker'), set::name('product'), set::items($products), set::value($v('product')), set::required(true)),
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->type), set::control('picker'), set::name('type'), set::items($lang->jxmarketaccess->typeList), set::value($v('type', 'listing')), set::required(true))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->name), set::name('name'), set::value($v('name')), set::required(true)),
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->code), set::name('code'), set::value($v('code')))
    ),
    formRow
    (
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->region), set::name('region'), set::value($v('region'))),
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->platform), set::name('platform'), set::value($v('platform'))),
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->package), set::name('package'), set::value($v('package')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->windowBegin), set::control('date'), set::name('windowBegin'), set::value($v('windowBegin'))),
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->windowEnd), set::control('date'), set::name('windowEnd'), set::value($v('windowEnd')))
    ),
    formRow
    (
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->quote), set::name('quote'), set::value($v('quote', 0))),
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->result), set::control('picker'), set::name('result'), set::items($lang->jxmarketaccess->resultList), set::value($v('result'))),
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->agreementNo), set::name('agreementNo'), set::value($v('agreementNo')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->owner), set::control('picker'), set::name('owner'), set::items($users), set::value($v('owner'))),
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->leadDept), set::control('picker'), set::name('leadDept'), set::items($depts), set::value($v('leadDept')))
    ),
    formRow
    (
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->supportDepts), picker(set::name('supportDepts[]'), set::items($depts), set::multiple(true), set::value($isEdit ? $row->supportDepts : ''))),
        formGroup(set::width('1/2'), set::label($lang->jxmarketaccess->requireCert), radioList(set::inline(true), set::name('requireCert'), set::items(array('1' => '是', '0' => '否')), set::value($v('requireCert', 1))))
    ),
    formRow
    (
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->begin), set::control('date'), set::name('begin'), set::value($v('begin', date('Y-m-d'))), set::required(true)),
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->end), set::control('date'), set::name('end'), set::value($v('end'))),
        formGroup(set::width('1/3'), set::label($lang->jxmarketaccess->budget), set::name('budget'), set::value($v('budget', 0)))
    ),
    formRow(formGroup(set::label($lang->jxmarketaccess->desc), textarea(set::name('desc'), set::rows(3), set::value($v('desc')))))
);
render();
