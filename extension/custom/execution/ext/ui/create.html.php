<?php
declare(strict_types=1);
/**
 * 添加任务执行：简洁版去掉关联计划；完整版去掉关联计划和需求/测试/执行/发布负责人。
 */
namespace zin;

$fields    = useFields('execution.create');
$typeField = $isStage ? 'attribute' : 'lifetime';
$fields->fullModeOrders('productsBox,team,teams,teamMembers[],desc');
$fields->remove('PO,QD,PM,RD');
if(!empty($project) && empty($project->hasProduct)) $fields->field('productsBox')->hidden(true);
if(!$isStage || empty($config->setPercent))
{
    $fields->remove('percent');
    $fields->field('days')->width('1/2');
}
if(empty($config->setCode))  $fields->remove('code');
if(!empty($config->setCode)) $fields->moveBefore($typeField, 'name');
if(!empty($project->model) && $project->model == 'kanban')
{
    $fields->remove($typeField);
    if(empty($config->setCode)) $fields->field('name')->wrapAfter();
}
if(!empty($project->model) && $project->model == 'agileplus')
{
    if(!empty($config->setCode))
    {
        $fields->field('code')->width('1/4');
        $fields->field($typeField)->width('1/4');
        $fields->moveAfter($typeField, 'code');
    }
    if($isKanban)
    {
        if(empty($config->setCode))  $fields->field('name')->width('full');
        if(!empty($config->setCode)) $fields->field('code')->width('1/2');
    }
}

jsVar('+projectID', $projectID);
jsVar('copyProjectID', $copyProjectID);
jsVar('window.weekend', $config->execution->weekend);
jsVar('isStage', $isStage);
jsVar('copyExecutionID', $copyExecutionID);
jsVar('executionID', isset($executionID) ? $executionID : 0);
jsVar('typeDesc', $lang->execution->typeDesc);

$showExecutionExec = !empty($from) and ($from == 'execution' || $from == 'doc');

formGridPanel
(
    set::title($showExecutionExec ? $lang->execution->createExec : $lang->execution->create),
    to::headingActions
    (
        btn
        (
            set::icon('copy'),
            setClass('primary-ghost size-md'),
            toggle::modal(array('target' => '#copyExecutionModal', 'destroyOnHide' => true, 'size' => 'sm')),
            $lang->execution->copyExec
        ),
        divider(setClass('h-4 mr-4 ml-2 self-center'))
    ),
    on::change('[name=project]', 'refreshPage'),
    on::change('[name=type]', 'setType'),
    on::change('[name=begin],[name=end]', 'computeWorkDays'),
    on::change('[name=teams]', 'loadMembers'),
    on::change('[name=parent]', 'setParentStage'),
    on::change('#copyTeam', 'toggleCopyTeam'),
    on::click('[name=lifetime]', 'toggleOpsTip'),
    set::fields($fields)
);

modalTrigger
(
    modal
    (
        set::id('copyExecutionModal'),
        set::footerClass('justify-center'),
        to::header
        (
            div
            (
                setClass('w-full'),
                div
                (
                    h4
                    (
                        set::className('copy-title'),
                        $lang->execution->copyTitle
                    )
                ),
                div
                (
                    setClass('flex items-center py-4 border-b border-b-1'),
                    span
                    (
                        setClass('mr-2'),
                        $lang->execution->selectProject
                    ),
                    picker
                    (
                        set::className('flex-1 w-full'),
                        set::name('project'),
                        set::items($copyProjects),
                        set::value($projectID),
                        set::required(true),
                        on::change('loadProjectExecutions')
                    )
                )
            )
        ),
        to::footer
        (
            setClass('mt-4'),
            btn
            (
                setClass('primary btn-wide hidden confirmBtn'),
                set::text($lang->execution->copyExec),
                on::click('setCopyExecution')
            )
        ),
        div
        (
            set::id('copyExecutions'),
            setClass('flex items-center flex-wrap gap-4')
        )
    )
);
