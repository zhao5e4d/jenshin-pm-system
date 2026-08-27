<?php
declare(strict_types=1);
namespace zin;

featureBar
(
    set::current($browseType),
    set::linkParams("browseType={key}")
);

toolbar
(
    hasPriv('jxproduct', 'create') ? btn
    (
        setClass('primary'),
        set::icon('plus'),
        set::url(createLink('jxproduct', 'create')),
        $lang->jxproduct->create
    ) : null
);

dtable
(
    set::cols(array_values($this->config->jxproduct->dtable->fieldList)),
    set::data(array_values($products)),
    set::orderBy($orderBy),
    set::sortLink(createLink('jxproduct', 'browse', "browseType={$browseType}&orderBy={name}_{sortType}&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}")),
    set::emptyTip($lang->noData),
    set::footPager(usePager())
);

render();
