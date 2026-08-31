<?php
declare(strict_types=1);
/**
 * 团队成就：第三项改为新增任务，不再展示测试用例。
 */
namespace zin;

$isEn               = $app->getClientLang() === 'en';
$yesterdayClassName = $isEn ? 'items-center text-gray border-r pr-2 col-reverse' : 'items-center text-gray border-r pr-2';
$todayClassName     = $isEn ? 'items-center text-success pl-2 col-reverse' : 'items-center text-success pl-2';
$createdTasksLabel  = !empty($lang->block->teamachievement->createdTasks) ? $lang->block->teamachievement->createdTasks : $lang->block->teamachievement->runCases;

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
    setClass('teamachievement-block'),
    set::bodyClass('ml-6'),
    div
    (
        setClass('flex flex-wrap gap-y-3'),
        div
        (
            setClass('flex mt-1 px-4 w-full item-row'),
            cell
            (
                set::width('100%'),
                setClass('flex'),
                cell
                (
                    set::width('50%'),
                    setClass('item-task px-1 w-1/2'),
                    div(setClass('h-0 w-0'), div(setClass('item-icon h-9 w-9'))),
                    div(setClass('text-gray pl-1'), $lang->block->teamachievement->finishedTasks),
                    div
                    (
                        setClass('mt-2 items-center flex pl-1'),
                        cell
                        (
                            setClass($yesterdayClassName),
                            span(setClass('text-base'), $lang->yesterday),
                            span(setClass('text-md pl-1 font-bold num'), $yesterdayTasks)
                        ),
                        cell
                        (
                            setClass($todayClassName),
                            span(setClass('text-base'), $lang->today),
                            span(setClass('text-md pl-1 font-bold num'), $finishedTasks)
                        )
                    )
                ),
                cell
                (
                    set::width('50%'),
                    setClass('item-story pl-8 w-1/2'),
                    div(setClass('h-0 w-0'), div(setClass('item-icon h-9 w-9'))),
                    div(setClass('h-0 w-0'), div(setClass('item-icon h-9 w-9'))),
                    div(setClass('text-gray pl-1'), $lang->block->teamachievement->createdStories),
                    div
                    (
                        setClass('mt-2 items-center flex pl-1'),
                        cell
                        (
                            setClass($yesterdayClassName),
                            span(setClass('text-base'), $lang->yesterday),
                            span(setClass('text-md pl-1 font-bold num'), $yesterdayStories)
                        ),
                        cell
                        (
                            setClass($todayClassName),
                            span(setClass('text-base'), $lang->today),
                            span(setClass('text-md pl-1 font-bold num'), $createdStories)
                        )
                    )
                )
            )
        ),
        div
        (
            setClass('flex px-4 w-full item-row'),
            cell
            (
                set::width('100%'),
                setClass('flex'),
                cell
                (
                    set::width('50%'),
                    setClass('item-newtask px-1 w-1/2'),
                    div(setClass('h-0 w-0'), div(setClass('item-icon h-9 w-9'))),
                    div(setClass('text-gray pl-1'), $createdTasksLabel),
                    div
                    (
                        setClass('mt-2 items-center flex pl-1'),
                        cell
                        (
                            setClass($yesterdayClassName),
                            span(setClass('text-base'), $lang->yesterday),
                            span(setClass('text-md pl-1 font-bold num'), $yesterdayCreatedTasks ?? $yesterdayCases)
                        ),
                        cell
                        (
                            setClass($todayClassName),
                            span(setClass('text-base'), $lang->today),
                            span(setClass('text-md pl-1 font-bold num'), $createdTasks ?? $runCases)
                        )
                    )
                ),
                cell
                (
                    set::width('50%'),
                    setClass('item-hour pl-8 w-1/2'),
                    div(setClass('h-0 w-0'), div(setClass('item-icon h-9 w-9'))),
                    div(setClass('text-gray pl-1'), $lang->block->teamachievement->consumedHours . ($isEn ? ' ' : ' / ') . $lang->block->projectstatistic->hour),
                    div
                    (
                        setClass('mt-2 items-center flex pl-1'),
                        cell
                        (
                            setClass($yesterdayClassName),
                            span(setClass('text-base'), $lang->yesterday),
                            span(setClass('text-md pl-1 font-bold num'), $yesterdayHours)
                        ),
                        cell
                        (
                            setClass($todayClassName),
                            span(setClass('text-base'), $lang->today),
                            span(setClass('text-md pl-1 font-bold num'), $consumedHours)
                        )
                    )
                )
            )
        )
    )
);

render();
