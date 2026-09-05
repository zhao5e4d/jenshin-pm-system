<?php
$lang->block->experience = 'Try now';

$lang->block->welcome->assignList = array();
$lang->block->welcome->assignList['task']         = 'Open Todos';
$lang->block->welcome->assignList['pendingStage'] = 'Open Tasks';
$lang->block->welcome->assignList['overdue']      = 'Overdue Projects';
$lang->block->welcome->assignList['blocker']      = 'At-Risk Projects';

$lang->block->teamachievement->createdTasks = 'Created Tasks';
$lang->block->teamachievement->runCases     = 'Created Tasks';

global $config;
if(empty($config->jenshin->enableHelp))
{
    unset($lang->block->moduleList['guide'], $lang->block->titleList['guide']);
    if(!empty($lang->block->default['full']['my']) && is_array($lang->block->default['full']['my']))
    {
        foreach($lang->block->default['full']['my'] as $jxIndex => $jxBlock)
        {
            if(($jxBlock['module'] ?? '') === 'guide' || ($jxBlock['code'] ?? '') === 'guide')
            {
                unset($lang->block->default['full']['my'][$jxIndex]);
            }
        }
    }
}

$lang->block->monthlyprogress->doneStoryEstimateTrendChart = 'Completed Task Hours Trend';
$lang->block->monthlyprogress->storyTrendChart             = 'Created and Completed Tasks Trend';
$lang->block->monthlyprogress->bugTrendChart               = 'Created and Completed Tasks Trend';
$lang->block->annualworkload->doneStoryEstimate            = 'Completed Task Hours';
$lang->block->annualworkload->doneStoryCount               = 'Completed Tasks';
$lang->block->annualworkload->resolvedBugCount             = 'Completed Tasks';
$lang->block->productlist->activatedBug        = 'Unfinished Tasks';

$lang->block->qastatistic->fixBugRate    = 'Task Completion Rate';
$lang->block->qastatistic->bugStatusStat = 'Monthly Task Trend';
$lang->block->bugstatistic->effective    = 'Total Tasks';
$lang->block->bugstatistic->fixed        = 'Completed';
$lang->block->bugstatistic->activated    = 'Unfinished';
$lang->block->tooltips['resolvedRate']   = "Task completion rate by {$lang->productCommon} = completed tasks / non-cancelled tasks";

if(!empty($lang->block->modules['product']->availableBlocks['bugstatistic']))
{
    $lang->block->modules['product']->availableBlocks['bugstatistic'] = "{$lang->productCommon} Task Statistics";
}
if(!empty($lang->block->modules['singleproduct']->availableBlocks['singlebugstatistic']))
{
    $lang->block->modules['singleproduct']->availableBlocks['singlebugstatistic'] = "{$lang->productCommon} Task Statistics";
}
