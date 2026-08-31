<?php
namespace zin;

global $lang;

$statData = data('statData');
$delayed  = (int)($statData->delayedCount ?? 0);

query('.bugCount')->replaceWith
(
    div
    (
        setClass('w-1/3 overdueCount'),
        div(setClass('text-md font-bold'), $delayed),
        span(setClass('text-gray'), $lang->project->overdueTasks)
    )
);
