<?php
global $config;

if(!isset($lang->resource)) $lang->resource = new stdclass();
$lang->resource->jxcollab = new stdclass();
$lang->resource->jxcollab->create   = 'createAction';
$lang->resource->jxcollab->edit     = 'editAction';
$lang->resource->jxcollab->view     = 'viewAction';
$lang->resource->jxcollab->feedback = 'feedbackAction';
$lang->resource->jxcollab->cancel   = 'cancelAction';

if(!isset($lang->jxcollab)) $lang->jxcollab = new stdclass();
$lang->jxcollab->createAction   = 'Start Collaboration';
$lang->jxcollab->editAction     = 'Edit Collaboration';
$lang->jxcollab->viewAction     = 'View Collaboration';
$lang->jxcollab->feedbackAction = 'Submit Collaboration Feedback';
$lang->jxcollab->cancelAction   = 'Cancel Collaboration';

$blocked = !empty($config->jenshin->blockedModules) && is_array($config->jenshin->blockedModules)
    ? $config->jenshin->blockedModules
    : array();
foreach($blocked as $mod)
{
    if(isset($lang->resource->$mod)) unset($lang->resource->$mod);
}
if(isset($lang->resource->execution))
{
    foreach(array('importBug', 'bug', 'testcase', 'testtask', 'testreport', 'build') as $jxMethod)
    {
        if(isset($lang->resource->execution->$jxMethod)) unset($lang->resource->execution->$jxMethod);
    }
}
if(isset($lang->resource->project))
{
    foreach(array('bug', 'testcase', 'testtask', 'testreport', 'build', 'release') as $jxMethod)
    {
        if(isset($lang->resource->project->$jxMethod)) unset($lang->resource->project->$jxMethod);
    }
}
if(empty($config->jenshin->enableLegacyBizMenus))
{
    foreach(array('jxproduct', 'jxregistration', 'jxmarketaccess', 'jxadmission', 'jxdashboard', 'jxcore') as $jxMod)
    {
        if(isset($lang->resource->$jxMod)) unset($lang->resource->$jxMod);
    }
}
