<?php
declare(strict_types=1);
namespace zin;

formPanel
(
    set::title($lang->jxcore->addcost),
    set::url(createLink('jxcore', 'addcost', "projectID={$projectID}")),
    formGroup(set::label($lang->jxcore->dept), set::control('picker'), set::name('dept'), set::items($depts)),
    formGroup(set::label($lang->jxcore->category), set::control('picker'), set::name('category'), set::items($lang->jxcore->costCategoryList), set::value('其他')),
    formGroup(set::label($lang->jxcore->amount), set::name('amount'), set::type('number'), set::required(true)),
    formGroup(set::label($lang->jxcore->occurDate), set::control('date'), set::name('occurDate'), set::value(date('Y-m-d'))),
    formGroup(set::label($lang->jxcore->desc), set::name('desc'))
);

render();
