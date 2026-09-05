<?php
declare(strict_types=1);
/**
 * 单个产品月度推进：第三张图图例用「完成」替代 Bug「解决」。
 */
namespace zin;

blockPanel
(
    to::titleSuffix
    (
        icon
        (
            setClass('text-light text-sm cursor-pointer'),
            toggle::tooltip
            (
                array
                (
                    'title'     => sprintf($lang->block->tooltips['metricTime'], $metricTime),
                    'placement' => 'bottom',
                    'type'      => 'white',
                    'className' => 'text-dark border border-light leading-5'
                )
            ),
            'help'
        )
    ),
    div
    (
        setClass('flex h-full w-full' . ($longBlock ? ' flex-nowrap' : ' flex-wrap')),
        cell
        (
            setClass('chart line-chart ' . ($longBlock ? 'py-2' : 'py-1 w-full')),
            set::width($longBlock ? '1/3' : '100%'),
            echarts
            (
                set::title(array('text' => $lang->block->monthlyprogress->doneStoryEstimateTrendChart, 'textStyle' => array('fontSize' => '12'))),
                set::color(array('#2B80FF', '#17CE97')),
                set::width('100%'),
                set::height(200),
                set::grid(array('left' => '10px', 'top' => '60px', 'right' => '0', 'bottom' => '0',  'containLabel' => true)),
                set::xAxis(array('type' => 'category', 'data' => array_keys($doneStoryEstimate), 'splitLine' => array('show' => false), 'axisTick' => array('alignWithLabel' => true, 'interval' => 0), 'axisLabel' => array('fontSize' => $longBlock ? '8' : '10'))),
                set::yAxis(array('type' => 'value', 'name' => "({$lang->hourCommon})", 'splitLine' => array('show' => false), 'axisLine' => array('show' => true, 'color' => '#DDD'), 'axisLabel' => array('showMaxLabel' => true, 'interval' => 'auto'))),
                set::series
                (
                    array
                    (
                        'type' => 'line',
                        'data' => array_values($doneStoryEstimate),
                        'emphasis' => array('label' => array('show' => true))
                    )
                )
            )
        ),
        cell
        (
            setClass('chart line-chart ' . ($longBlock ? 'py-2' : 'py-1 w-full')),
            set::width($longBlock ? '1/3' : '100%'),
            echarts
            (
                set::title(array('text' => $lang->block->monthlyprogress->storyTrendChart, 'textStyle' => array('fontSize' => '12'))),
                set::color(array('#2B80FF', '#17CE97')),
                set::width('100%'),
                set::height(200),
                set::grid(array('left' => '10px', 'top' => '60px', 'right' => '0', 'bottom' => '0',  'containLabel' => true)),
                set::legend(array('show' => true, 'right' => '0', 'top' => '25px', 'textStyle' => array('fontSize' => '11'))),
                set::xAxis(array('type' => 'category', 'data' => array_keys($createStoryCount), 'splitLine' => array('show' => false), 'axisTick' => array('alignWithLabel' => true, 'interval' => 0), 'axisLabel' => array('fontSize' => $longBlock ? '8' : '10'))),
                set::yAxis(array('type' => 'value', 'name' => "({$lang->block->projectstatistic->unit})", 'splitLine' => array('show' => false), 'axisLine' => array('show' => true, 'color' => '#DDD'), 'axisLabel' => array('showMaxLabel' => true, 'interval' => 'auto'))),
                set::series
                (
                    array
                    (
                        array
                        (
                            'type' => 'line',
                            'name' => $lang->block->productstatistic->opened,
                            'data' => array_values($createStoryCount),
                            'emphasis' => array('label' => array('show' => true))
                        ),
                        array
                        (
                            'type' => 'line',
                            'name' => $lang->block->productstatistic->done,
                            'data' => array_values($doneStoryCount),
                            'emphasis' => array('label' => array('show' => true))
                        )
                    )
                )
            )
        ),
        cell
        (
            setClass('chart line-chart ' . ($longBlock ? 'py-2' : 'py-1 w-full')),
            set::width($longBlock ? '1/3' : '100%'),
            echarts
            (
                set::title(array('text' => $lang->block->monthlyprogress->bugTrendChart, 'textStyle' => array('fontSize' => '12'))),
                set::color(array('#2B80FF', '#17CE97')),
                set::width('100%'),
                set::height(200),
                set::grid(array('left' => '10px', 'top' => '60px', 'right' => '0', 'bottom' => '0', 'containLabel' => true)),
                set::legend(array('show' => true, 'right' => '0', 'top' => '25px', 'textStyle' => array('fontSize' => '11'))),
                set::xAxis(array('type' => 'category', 'data' => array_keys($createBugCount), 'splitLine' => array('show' => false), 'axisTick' => array('alignWithLabel' => true, 'interval' => 0), 'axisLabel' => array('fontSize' => $longBlock ? '8' : '10'))),
                set::yAxis(array('type' => 'value', 'name' => "({$lang->block->projectstatistic->unit})", 'splitLine' => array('show' => false), 'axisLine' => array('show' => true, 'color' => '#DDD'), 'axisLabel' => array('showMaxLabel' => true, 'interval' => 'auto'))),
                set::series
                (
                    array
                    (
                        array
                        (
                            'type' => 'line',
                            'name' => $lang->block->productstatistic->opened,
                            'data' => array_values($createBugCount),
                            'emphasis' => array('label' => array('show' => true))
                        ),
                        array
                        (
                            'type' => 'line',
                            'name' => $lang->block->productstatistic->done,
                            'data' => array_values($fixedBugCount),
                            'emphasis' => array('label' => array('show' => true))
                        )
                    )
                )
            )
        )
    )
);

render();
