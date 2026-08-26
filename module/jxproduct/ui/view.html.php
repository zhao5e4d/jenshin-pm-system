<?php
declare(strict_types=1);
namespace zin;

detailHeader
(
    to::prefix
    (
        backBtn($lang->goback, setClass('secondary'), set::icon('back')),
        entityLabel(set::entityID($product->id), set::text($product->name), set::level(1))
    ),
    to::suffix
    (
        hasPriv('jxproduct', 'edit') ? btn(set::url(createLink('jxproduct', 'edit', "id={$product->id}")), set::icon('edit'), $lang->edit) : null
    )
);

$fieldItems = array(
    $lang->jxproduct->model        => $product->model,
    $lang->jxproduct->category     => $product->category,
    $lang->jxproduct->line         => $product->line,
    $lang->jxproduct->certNo       => $product->certNo,
    $lang->jxproduct->certValidTo  => $product->certValidTo,
    $lang->jxproduct->udi          => $product->udi,
    $lang->jxproduct->tenderCode   => $product->tenderCode,
    $lang->jxproduct->manufacturer => $product->manufacturer,
    $lang->jxproduct->specs        => $product->specs,
    $lang->jxproduct->patents      => $product->patents
);

$matterLink = function($module, $rows) use ($lang)
{
    $items = array();
    foreach($rows as $row)
    {
        $items[] = li(a(set::href(createLink($module, 'view', "id={$row->id}")), $row->name . ' · ' . zget($row, 'status', '')));
    }
    return $items ? ul($items) : div($lang->noData);
};

detailBody
(
    sectionList
    (
        section
        (
            set::title($lang->jxproduct->common),
            tableData
            (
                set::trClass('h-10'),
                array_map(function($label, $value)
                {
                    return item(set::name($label), $value ?: '-');
                }, array_keys($fieldItems), array_values($fieldItems))
            )
        ),
        section(set::title($lang->jxproduct->desc), set::content($product->desc ?: $lang->noDesc), set::useHtml(true)),
        section(set::title('产品注册'), $matterLink('jxregistration', $regs)),
        section(set::title('市场准入'), $matterLink('jxmarketaccess', $access)),
        section(set::title('推广入院'), $matterLink('jxadmission', $admits))
    )
);

render();
