<?php
declare(strict_types=1);
namespace zin;

$items = array(item(set::id('all'), set::text($lang->jxregistration->all)));
foreach($lang->jxregistration->typeList as $key => $text) $items[] = item(set::id($key), set::text($text));

featureBar(set::current($browseType), set::linkParams("browseType={key}"), $items);

toolbar
(
    hasPriv('jxregistration', 'create') ? btn(setClass('primary'), set::icon('plus'), set::url(createLink('jxregistration', 'create')), $lang->jxregistration->create) : null
);

dtable
(
    set::cols($this->config->jxregistration->dtable->fieldList),
    set::data(array_values($rows)),
    set::emptyTip($lang->noData),
    set::footPager(usePager())
);

render();
