<?php
namespace zin;

global $lang;

$statData = data('statData');
$delayed  = (int)($statData->delayedCount ?? 0);

query('.justify-center.items-center.pl-4.py-2')->append
(
    div
    (
        setClass('w-1/3 overdueCount'),
        div(setClass('text-lg font-bold'), $delayed),
        $lang->execution->overdueTasks
    )
);
