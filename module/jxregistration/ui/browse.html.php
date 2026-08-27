<?php
declare(strict_types=1);
namespace zin;

featureBar(set::current($browseType), set::linkParams("browseType={key}"));

toolbar
(
    hasPriv('jxregistration', 'create') ? btn(setClass('primary'), set::icon('plus'), set::url(createLink('jxregistration', 'create')), $lang->jxregistration->create) : null
);

dtable
(
    set::cols(array_values($this->config->jxregistration->dtable->fieldList)),
    set::data(array_values($rows)),
    set::emptyTip($lang->noData),
    set::footPager(usePager())
);

render();
