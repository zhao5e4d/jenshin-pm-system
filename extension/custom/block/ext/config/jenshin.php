<?php
if(isset($config->block->projectstatistic->items['bug'])) unset($config->block->projectstatistic->items['bug']);

/* 工作台「指派给我」增加「待我反馈」页签，列任务协同里等我表态的协同单。 */
global $lang, $app;
$app->loadLang('jxcollab');

$config->block->jxcollab = new stdclass();
$config->block->jxcollab->dtable = new stdclass();
$config->block->jxcollab->dtable->fieldList = array();
$config->block->jxcollab->dtable->fieldList['id']    = array('name' => 'id', 'title' => $lang->idAB, 'type' => 'id', 'fixed' => 'left', 'width' => '60px');
$config->block->jxcollab->dtable->fieldList['title'] = array('name' => 'title', 'title' => $lang->jxcollab->title, 'type' => 'title', 'fixed' => 'left', 'link' => array('module' => 'jxcollab', 'method' => 'view', 'params' => 'collabID={id}'), 'data-toggle' => 'modal', 'data-size' => 'lg');
$config->block->jxcollab->dtable->fieldList['typeName']    = array('name' => 'typeName', 'title' => $lang->jxcollab->type, 'width' => '90px');
$config->block->jxcollab->dtable->fieldList['taskName']    = array('name' => 'taskName', 'title' => $lang->jxcollab->task, 'flex' => 1);
$config->block->jxcollab->dtable->fieldList['projectName'] = array('name' => 'projectName', 'title' => $lang->jxcollab->project, 'flex' => 1);
$config->block->jxcollab->dtable->fieldList['deadline']    = array('name' => 'deadline', 'title' => $lang->jxcollab->deadline, 'type' => 'date', 'width' => '100px');
$config->block->jxcollab->dtable->fieldList['openedBy']    = array('name' => 'openedBy', 'title' => $lang->jxcollab->openedBy, 'type' => 'user', 'width' => '100px');

$config->block->jxcollab->dtable->short = new stdclass();
$config->block->jxcollab->dtable->short->fieldList = array();
$config->block->jxcollab->dtable->short->fieldList['id']    = $config->block->jxcollab->dtable->fieldList['id'];
$config->block->jxcollab->dtable->short->fieldList['title'] = $config->block->jxcollab->dtable->fieldList['title'];
$config->block->jxcollab->dtable->short->fieldList['deadline'] = $config->block->jxcollab->dtable->fieldList['deadline'];

/* 项目计划看板按超长区块铺满整行。 */
if(isset($config->block->size['waterfallproject']['waterfallgantt']))
{
    $config->block->size['waterfallproject']['waterfallgantt'] = array(3 => 8, 2 => 8, 1 => 8);
}
