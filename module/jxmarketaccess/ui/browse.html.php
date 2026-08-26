<?php
declare(strict_types=1);
namespace zin;

$items = array(item(set::id('all'), set::text($lang->jxmarketaccess->all)));
foreach($lang->jxmarketaccess->typeList as $key => $text) $items[] = item(set::id($key), set::text($text));
featureBar(set::current($browseType), set::linkParams("browseType={key}"), $items);
toolbar(hasPriv('jxmarketaccess', 'create') ? btn(setClass('primary'), set::icon('plus'), set::url(createLink('jxmarketaccess', 'create')), $lang->jxmarketaccess->create) : null);
dtable(set::cols($this->config->jxmarketaccess->dtable->fieldList), set::data(array_values($rows)), set::footPager(usePager()));
render();
