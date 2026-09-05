<?php
global $config;
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
