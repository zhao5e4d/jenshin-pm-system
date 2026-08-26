<?php
declare(strict_types=1);
namespace zin;

featureBar
(
    set::current($browseType),
    set::linkParams("browseType={key}"),
    item(set::id('all'), set::text($lang->jxproduct->all)),
    item(set::id('expiring'), set::text($lang->jxproduct->expiring))
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
    set::cols($this->config->jxproduct->dtable->fieldList),
    set::data(array_values($products)),
    set::orderBy($orderBy),
    set::sortLink(createLink('jxproduct', 'browse', "browseType={$browseType}&orderBy={name}_{sortType}&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}")),
    set::footPager(usePager())
);

render();
