<?php
declare(strict_types=1);
namespace zin;
$hospRows = array();
foreach($hospitals as $hospital)
{
    $hospRows[] = h::tr(h::td($hospital->name), h::td($hospital->level), h::td($hospital->province . ' ' . $hospital->city), h::td($hospital->department));
}
div
(
    setClass('p-4'),
    h::h3($lang->jxadmission->hospital),
    formPanel
    (
        set::title($lang->jxadmission->hospital),
        set::url(createLink('jxadmission', 'hospital')),
        formRow
        (
            formGroup(set::width('1/2'), set::label($lang->name), set::name('name'), set::required(true)),
            formGroup(set::width('1/2'), set::label($lang->jxadmission->level), set::name('level'))
        ),
        formRow
        (
            formGroup(set::width('1/3'), set::label($lang->jxadmission->province), set::name('province')),
            formGroup(set::width('1/3'), set::label($lang->jxadmission->city), set::name('city')),
            formGroup(set::width('1/3'), set::label($lang->jxadmission->department), set::name('department'))
        )
    ),
    h::table(setClass('table bordered mt-4'), h::thead(h::tr(h::th($lang->name), h::th($lang->jxadmission->level), h::th($lang->jxadmission->province), h::th($lang->jxadmission->department))), h::tbody($hospRows))
);
render();
