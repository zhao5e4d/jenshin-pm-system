<?php
if(!isset($lang->jxproduct))      $lang->jxproduct      = new stdclass();
if(!isset($lang->jxregistration)) $lang->jxregistration = new stdclass();
if(!isset($lang->jxmarketaccess)) $lang->jxmarketaccess = new stdclass();
if(!isset($lang->jxadmission))    $lang->jxadmission    = new stdclass();
if(!isset($lang->jxdashboard))    $lang->jxdashboard    = new stdclass();
if(!isset($lang->jxboard))        $lang->jxboard        = new stdclass();
if(!isset($lang->jxcore))         $lang->jxcore         = new stdclass();
if(!isset($lang->resource))       $lang->resource       = new stdclass();

$lang->resource->jxproduct = new stdclass();
$lang->resource->jxproduct->browse = 'browseAction';
$lang->resource->jxproduct->create = 'createAction';
$lang->resource->jxproduct->edit   = 'editAction';
$lang->resource->jxproduct->view   = 'viewAction';

$lang->resource->jxregistration = new stdclass();
$lang->resource->jxregistration->browse = 'browseAction';
$lang->resource->jxregistration->create = 'createAction';
$lang->resource->jxregistration->edit   = 'editAction';
$lang->resource->jxregistration->view   = 'viewAction';

$lang->resource->jxmarketaccess = new stdclass();
$lang->resource->jxmarketaccess->browse = 'browseAction';
$lang->resource->jxmarketaccess->create = 'createAction';
$lang->resource->jxmarketaccess->edit   = 'editAction';
$lang->resource->jxmarketaccess->view   = 'viewAction';

$lang->resource->jxadmission = new stdclass();
$lang->resource->jxadmission->browse   = 'browseAction';
$lang->resource->jxadmission->create   = 'createAction';
$lang->resource->jxadmission->edit     = 'editAction';
$lang->resource->jxadmission->view     = 'viewAction';
$lang->resource->jxadmission->hospital = 'hospitalAction';

$lang->resource->jxdashboard = new stdclass();
$lang->resource->jxdashboard->index     = 'indexAction';
$lang->resource->jxdashboard->overview  = 'overviewAction';
$lang->resource->jxdashboard->dept      = 'deptAction';
$lang->resource->jxdashboard->portfolio = 'portfolioAction';
$lang->resource->jxdashboard->meeting   = 'meetingAction';

$lang->resource->jxboard = new stdclass();
$lang->resource->jxboard->index     = 'indexAction';
$lang->resource->jxboard->overview  = 'overviewAction';
$lang->resource->jxboard->dept      = 'deptAction';
$lang->resource->jxboard->portfolio = 'portfolioAction';
$lang->resource->jxboard->meeting   = 'meetingAction';

$lang->resource->jxcore = new stdclass();
$lang->resource->jxcore->togglecheck  = 'togglecheck';
$lang->resource->jxcore->submitstage  = 'submitstage';
$lang->resource->jxcore->approvestage = 'approvestage';
$lang->resource->jxcore->addcost      = 'addcost';

$lang->jxproduct->browseAction = '浏览产品档案';
$lang->jxproduct->createAction = '新建产品档案';
$lang->jxproduct->editAction   = '编辑产品档案';
$lang->jxproduct->viewAction   = '查看产品档案';
$lang->jxregistration->browseAction = '浏览注册事项';
$lang->jxregistration->createAction = '新建注册事项';
$lang->jxregistration->editAction   = '编辑注册事项';
$lang->jxregistration->viewAction   = '查看注册事项';
$lang->jxmarketaccess->browseAction = '浏览准入事项';
$lang->jxmarketaccess->createAction = '新建准入事项';
$lang->jxmarketaccess->editAction   = '编辑准入事项';
$lang->jxmarketaccess->viewAction   = '查看准入事项';
$lang->jxadmission->browseAction   = '浏览入院事项';
$lang->jxadmission->createAction   = '新建入院事项';
$lang->jxadmission->editAction     = '编辑入院事项';
$lang->jxadmission->viewAction     = '查看入院事项';
$lang->jxadmission->hospitalAction = '维护医院';
$lang->jxdashboard->indexAction     = '查看看板';
$lang->jxdashboard->overviewAction  = '经营总览';
$lang->jxdashboard->deptAction      = '部门经营';
$lang->jxdashboard->portfolioAction = '项目组合';
$lang->jxdashboard->meetingAction   = '会议视图';
if(!isset($lang->group)) $lang->group = new stdclass();
if(!isset($lang->group->package)) $lang->group->package = new stdclass();
$lang->group->package->browseJxboard = '浏览';

$lang->jxboard->indexAction     = '查看看板';
$lang->jxboard->overviewAction  = '经营总览';
$lang->jxboard->deptAction      = '部门经营';
$lang->jxboard->portfolioAction = '项目组合';
$lang->jxboard->meetingAction   = '会议视图';
$lang->jxcore->togglecheck  = '完成检查项';
$lang->jxcore->submitstage  = '提交阶段门';
$lang->jxcore->approvestage = '审批阶段门';
$lang->jxcore->addcost      = '登记费用';

$blocked = array('qa','bug','testcase','testtask','testsuite','testreport','caselib','repo','git','gitlab','gogs','gitea','gitfox','jenkins','pipeline','codescan','ppm','ci','compile','sonarqube','zahost','zanode','build','release','projectrelease','projectbuild','branch','space','artifact','design');
foreach($blocked as $mod)
{
    if(isset($lang->resource->$mod)) unset($lang->resource->$mod);
}
