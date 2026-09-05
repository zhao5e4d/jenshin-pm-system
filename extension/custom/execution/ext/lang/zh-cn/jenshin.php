<?php
$lang->execution->overdueTasks = '逾期任务';
unset($lang->execution->lifeTimeList['ops']);
$lang->execution->typeList['sprint'] = $lang->executionCommon;
$lang->execution->createExec         = "添加{$lang->executionCommon}";
$lang->execution->copyExec           = "复制{$lang->executionCommon}";
$lang->execution->execName           = "{$lang->executionCommon}名称";
$lang->execution->execType           = "{$lang->executionCommon}类型";
$lang->execution->execDesc           = "{$lang->executionCommon}描述";
$lang->execution->execPM             = "{$lang->executionCommon}负责人";
$lang->execution->execStatus         = "{$lang->executionCommon}状态";
$lang->execution->execId             = "{$lang->executionCommon}编号";
$lang->execution->execCode           = "{$lang->executionCommon}代号";
$lang->execution->allExecutions      = "所有{$lang->executionCommon}";
$lang->execution->allExecutionAB     = "{$lang->executionCommon}列表";
$lang->execution->gobackExecution    = "返回{$lang->executionCommon}列表";
$lang->execution->noExecutions       = "暂时没有{$lang->executionCommon}。";

global $config;
if(empty($config->jenshin->enableHelp)) $lang->execution->howToUpdateBurn = '';
