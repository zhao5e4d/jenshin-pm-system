<?php
$lang->jxproduct = new stdclass();
$lang->jxproduct->common      = '产品档案';
$lang->jxproduct->browse      = '产品档案';
$lang->jxproduct->create      = '新建产品';
$lang->jxproduct->edit        = '编辑产品';
$lang->jxproduct->view        = '产品详情';
$lang->jxproduct->name        = '产品名称';
$lang->jxproduct->model       = '型号';
$lang->jxproduct->category    = '管理类别';
$lang->jxproduct->line        = '产品线';
$lang->jxproduct->certNo      = '注册证号';
$lang->jxproduct->certValidTo = '证照有效期';
$lang->jxproduct->specs       = '规格';
$lang->jxproduct->udi         = 'UDI';
$lang->jxproduct->manufacturer= '生产主体';
$lang->jxproduct->patents     = '知识产权';
$lang->jxproduct->tenderCode  = '集采编码';
$lang->jxproduct->desc        = '说明';
$lang->jxproduct->related     = '关联事项';
$lang->jxproduct->all         = '全部';
$lang->jxproduct->expiring    = '证照临期';

$lang->jxproduct->categoryList['']     = '';
$lang->jxproduct->categoryList['一类'] = '一类';
$lang->jxproduct->categoryList['二类'] = '二类';
$lang->jxproduct->categoryList['三类'] = '三类';

$lang->jxproduct->errorSaveArchive = '产品档案保存失败，请重试或联系管理员。';
$lang->jxproduct->certExpiring     = '临期';
$lang->jxproduct->certExpired      = '已过期';

$lang->jxproduct->browseAction = '浏览产品档案';
$lang->jxproduct->createAction = '新建产品档案';
$lang->jxproduct->editAction   = '编辑产品档案';
$lang->jxproduct->viewAction   = '查看产品档案';

$lang->jxproduct->menu = new stdclass();
$lang->jxproduct->menu->browse = array('link' => "{$lang->jxproduct->browse}|jxproduct|browse|", 'alias' => 'view,edit');
$lang->jxproduct->menu->create = array('link' => "{$lang->jxproduct->create}|jxproduct|create|");
$lang->jxproduct->menuOrder[5]  = 'browse';
$lang->jxproduct->menuOrder[10] = 'create';

$lang->jxproduct->featureBar = array();
$lang->jxproduct->featureBar['browse']['all']      = $lang->jxproduct->all;
$lang->jxproduct->featureBar['browse']['expiring'] = $lang->jxproduct->expiring;
