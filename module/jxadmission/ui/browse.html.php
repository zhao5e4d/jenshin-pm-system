<?php
declare(strict_types=1);
namespace zin;
featureBar(set::current($browseType), set::linkParams("browseType={key}"), item(set::id('all'), set::text($lang->jxadmission->all)), item(set::id('doing'), set::text($lang->jxadmission->doing)));
toolbar(
    hasPriv('jxadmission', 'hospital') ? btn(set::url(createLink('jxadmission', 'hospital')), set('data-toggle', 'modal'), set('data-size', 'lg'), $lang->jxadmission->hospital) : null,
    hasPriv('jxadmission', 'create') ? btn(setClass('primary'), set::icon('plus'), set::url(createLink('jxadmission', 'create')), $lang->jxadmission->create) : null
);
dtable(set::cols($this->config->jxadmission->dtable->fieldList), set::data(array_values($rows)), set::footPager(usePager()));
render();
