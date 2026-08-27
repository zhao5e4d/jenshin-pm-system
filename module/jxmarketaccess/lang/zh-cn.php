<?php
global $app;
if(empty($lang->jxcore->statusList ?? null)) $app->loadLang('jxcore');
$lang->jxmarketaccess = new stdclass();
$lang->jxmarketaccess->common       = '市场准入';
$lang->jxmarketaccess->browse       = '准入事项';
$lang->jxmarketaccess->create       = '新建准入事项';
$lang->jxmarketaccess->edit         = '编辑准入事项';
$lang->jxmarketaccess->view         = '准入详情';
$lang->jxmarketaccess->product      = '产品';
$lang->jxmarketaccess->name         = '事项名称';
$lang->jxmarketaccess->code         = '事项编码';
$lang->jxmarketaccess->type         = '事项类型';
$lang->jxmarketaccess->region       = '区域';
$lang->jxmarketaccess->platform     = '平台';
$lang->jxmarketaccess->package      = '标段/目录';
$lang->jxmarketaccess->windowBegin  = '窗口开始';
$lang->jxmarketaccess->windowEnd    = '窗口截止';
$lang->jxmarketaccess->quote        = '报价';
$lang->jxmarketaccess->result       = '结果';
$lang->jxmarketaccess->agreementNo  = '协议号';
$lang->jxmarketaccess->fulfillBegin = '履约开始';
$lang->jxmarketaccess->fulfillEnd   = '履约结束';
$lang->jxmarketaccess->requireCert  = '需取证前置';
$lang->jxmarketaccess->status       = '状态';
$lang->jxmarketaccess->owner        = '负责人';
$lang->jxmarketaccess->leadDept     = '主责部门';
$lang->jxmarketaccess->supportDepts = '协作部门';
$lang->jxmarketaccess->begin        = '开始日期';
$lang->jxmarketaccess->end          = '计划完成';
$lang->jxmarketaccess->budget       = '预算(万元)';
$lang->jxmarketaccess->desc         = '说明';
$lang->jxmarketaccess->all          = '全部';
$lang->jxmarketaccess->stages       = '阶段门';
$lang->jxmarketaccess->costs        = '经费台账';

$lang->jxmarketaccess->typeList['listing']      = '挂网';
$lang->jxmarketaccess->typeList['centralized']  = '集采申报';
$lang->jxmarketaccess->typeList['tender']       = '招投标';

$lang->jxmarketaccess->resultList['']        = '';
$lang->jxmarketaccess->resultList['pending'] = '进行中';
$lang->jxmarketaccess->resultList['won']     = '中选';
$lang->jxmarketaccess->resultList['lost']    = '未中选';
$lang->jxmarketaccess->resultList['listed']  = '已挂网';

$lang->jxmarketaccess->browseAction = '浏览准入事项';
$lang->jxmarketaccess->createAction = '新建准入事项';
$lang->jxmarketaccess->editAction   = '编辑准入事项';
$lang->jxmarketaccess->viewAction   = '查看准入事项';

$lang->jxmarketaccess->menu = new stdclass();
$lang->jxmarketaccess->menu->browse = array('link' => "{$lang->jxmarketaccess->browse}|jxmarketaccess|browse|", 'alias' => 'view,edit');
$lang->jxmarketaccess->menu->create = array('link' => "{$lang->jxmarketaccess->create}|jxmarketaccess|create|");
$lang->jxmarketaccess->menuOrder[5]  = 'browse';
$lang->jxmarketaccess->menuOrder[10] = 'create';

$lang->jxmarketaccess->featureBar = array();
$lang->jxmarketaccess->featureBar['browse']['all'] = $lang->jxmarketaccess->all;
foreach($lang->jxmarketaccess->typeList as $key => $text) $lang->jxmarketaccess->featureBar['browse'][$key] = $text;
