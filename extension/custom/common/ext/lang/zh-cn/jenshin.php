<?php
/**
 * 品牌 + 一级菜单。菜单名与结构恢复自 WSL 旧工程
 * /home/simpro/work/jenshin-pm-system/extension/custom/common/ext/lang/zh-cn/jenshin.php
 */
global $config;

$lang->zentaoPMS = '健忻医疗项目管理系统';
$lang->logoImg  = 'jx-logo.png';
$lang->jenshinCopyright = '© 健忻科技 ' . date('Y');

if(!isset($lang->jxproduct))      $lang->jxproduct      = new stdclass();
if(!isset($lang->jxregistration)) $lang->jxregistration = new stdclass();
if(!isset($lang->jxmarketaccess)) $lang->jxmarketaccess = new stdclass();
if(!isset($lang->jxadmission))    $lang->jxadmission    = new stdclass();
if(!isset($lang->jxdashboard))    $lang->jxdashboard    = new stdclass();
if(!isset($lang->jxboard))        $lang->jxboard        = new stdclass();
if(!isset($lang->jxcore))         $lang->jxcore         = new stdclass();

$lang->jxproduct->common      = '产品档案';
$lang->jxregistration->common = '产品注册';
$lang->jxmarketaccess->common = '市场准入';
$lang->jxadmission->common    = '推广入院';
$lang->jxdashboard->common    = '旧数据看板';
$lang->jxboard->common        = '数据看板';
$lang->jxcore->common         = '医疗项目';

$lang->navIcons['jxproduct']      = "<i class='icon icon-product'></i>";
$lang->navIcons['jxregistration'] = "<i class='icon icon-verified'></i>";
$lang->navIcons['jxmarketaccess'] = "<i class='icon icon-stack'></i>";
$lang->navIcons['jxadmission']    = "<i class='icon icon-group'></i>";
$lang->navIcons['jxdashboard']    = "<i class='icon icon-statistic'></i>";
$lang->navIcons['jxboard']        = "<i class='icon icon-bars'></i>";
$lang->navIconNames['jxproduct']      = 'product';
$lang->navIconNames['jxregistration'] = 'verified';
$lang->navIconNames['jxmarketaccess'] = 'stack';
$lang->navIconNames['jxadmission']    = 'group';
$lang->navIconNames['jxdashboard']    = 'statistic';
$lang->navIconNames['jxboard']        = 'bars';

$executionLink = !empty($config->executionLink) ? $config->executionLink : 'execution-all';
list($executionModule, $executionMethod) = explode('-', $executionLink);

$lang->mainNav = new stdclass();
$lang->mainNav->my             = "{$lang->navIcons['my']} 工作台|my|index|";
$lang->mainNav->product        = "{$lang->navIcons['product']} 产品组合|product|all|";
$lang->mainNav->project        = "{$lang->navIcons['project']} 项目管理|project|browse|";
$lang->mainNav->execution      = "{$lang->navIcons['execution']} 任务执行|$executionModule|$executionMethod|";
$lang->mainNav->jxboard        = "{$lang->navIcons['jxboard']} 数据看板|jxboard|overview|";
$lang->mainNav->jxdashboard    = "{$lang->navIcons['jxdashboard']} 旧数据看板|jxdashboard|overview|";
$lang->mainNav->jxregistration = "{$lang->navIcons['jxregistration']} 产品注册|jxregistration|browse|";
$lang->mainNav->jxmarketaccess = "{$lang->navIcons['jxmarketaccess']} 市场准入|jxmarketaccess|browse|";
$lang->mainNav->jxadmission    = "{$lang->navIcons['jxadmission']} 推广入院|jxadmission|browse|";
$lang->mainNav->doc            = "{$lang->navIcons['doc']} 文档空间|doc|index|";
$lang->mainNav->system         = "{$lang->navIcons['system']} 组织部门|my|team|";
$lang->mainNav->admin          = "{$lang->navIcons['admin']} 组织设置|admin|index|";

$lang->mainNav->menuOrder = array();
$lang->mainNav->menuOrder[5]  = 'my';
$lang->mainNav->menuOrder[10] = 'product';
$lang->mainNav->menuOrder[15] = 'project';
$lang->mainNav->menuOrder[18] = 'execution';
$lang->mainNav->menuOrder[19] = 'jxboard';
$lang->mainNav->menuOrder[20] = 'jxregistration';
$lang->mainNav->menuOrder[25] = 'jxmarketaccess';
$lang->mainNav->menuOrder[30] = 'jxadmission';
$lang->mainNav->menuOrder[38] = 'jxdashboard';
$lang->mainNav->menuOrder[40] = 'doc';
$lang->mainNav->menuOrder[50] = 'system';
$lang->mainNav->menuOrder[60] = 'admin';
$lang->dividerMenu = ',doc,admin,';

/* 默认不注册四个旧业务菜单，模块与语言项保留。 */
if(empty($config->jenshin->enableLegacyBizMenus))
{
    unset($lang->mainNav->jxregistration, $lang->mainNav->jxmarketaccess, $lang->mainNav->jxadmission, $lang->mainNav->jxdashboard);
    unset($lang->mainNav->menuOrder[20], $lang->mainNav->menuOrder[25], $lang->mainNav->menuOrder[30], $lang->mainNav->menuOrder[38]);
}

/* 工作台二级菜单「SSH密钥」。 */
if(empty($config->jenshin->enableSSH) && isset($lang->my->menu))
{
    unset($lang->my->menu->ssh, $lang->my->menuOrder[55]);
    if(!empty($lang->my->dividerMenu)) $lang->my->dividerMenu = str_replace(',ssh,', ',', $lang->my->dividerMenu);
}

$lang->navGroup->jxproduct      = 'product';
$lang->navGroup->jxregistration = 'jxregistration';
$lang->navGroup->jxmarketaccess = 'jxmarketaccess';
$lang->navGroup->jxadmission    = 'jxadmission';
$lang->navGroup->jxdashboard    = 'jxdashboard';
$lang->navGroup->jxboard        = 'jxboard';
$lang->navGroup->jxcore         = 'jxdashboard';
$lang->navGroup->jxhospital     = 'jxadmission';

/* 项目详情二级菜单：测试 / 构建 / 发布。setMenu 还会再裁一次（含 design）。 */
if(function_exists('jxHideProjectMenus') && !empty($config->jenshin->hiddenProjectMenus))
{
    jxHideProjectMenus($lang, $config->jenshin->hiddenProjectMenus);
}
