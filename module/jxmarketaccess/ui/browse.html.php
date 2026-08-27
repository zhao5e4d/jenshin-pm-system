<?php
declare(strict_types=1);
namespace zin;

featureBar(set::current($browseType), set::linkParams("browseType={key}"));
toolbar(hasPriv('jxmarketaccess', 'create') ? btn(setClass('primary'), set::icon('plus'), set::url(createLink('jxmarketaccess', 'create')), $lang->jxmarketaccess->create) : null);

dtable
(
    set::cols(array_values($this->config->jxmarketaccess->dtable->fieldList)),
    set::data(array_values($rows)),
    set::emptyTip($lang->noData),
    set::footPager(usePager())
);

render();
