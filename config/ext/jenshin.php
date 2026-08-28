<?php
/**
 * Jenshin medical PM overlay. Loaded after zentaopms.php / my.php.
 * Keep ZenTao core files intact; all custom constants and switches live here.
 */
if(!isset($config->zin)) $config->zin = new stdclass();
$config->zin->extraCSS = 'jenshin.css';

$config->jenshin = new stdclass();
$config->jenshin->version = '1.0.0';
$config->jenshin->edition = 'medical-pm';

/* 首页 1.0 引导：extension/custom/misc/ext/config/jenshin.php，配图 www/static/svg/jenshin/ */

/* 默认萱萱紫：与当前靛紫玻璃语言同一套色阶。 */
if(isset($config->default)) $config->default->theme = 'purple';

$config->jenshin->blockedModules = array(
    'qa', 'bug', 'testcase', 'testtask', 'testsuite', 'testreport', 'caselib', 'automation',
    'repo', 'git', 'gitlab', 'gogs', 'gitea', 'gitfox', 'jenkins', 'pipeline', 'codescan',
    'ppm', 'ci', 'compile', 'sonarqube', 'zahost', 'zanode', 'space', 'artifact',
    'build', 'release', 'projectrelease', 'projectbuild', 'branch', 'repobranchtype',
    'repobranchrule', 'reporeviewflow', 'design'
);

$config->jenshin->blockedFeatures = 'otherDevOps,qaTestsuite,qaAutomated,qaCaselib,otherAI,productRoadmap,productTrack,productUR,productER,myScore';

/* 创建项目默认 Scrum，跳过「选择项目管理方式」弹窗，并锁定模型不可切换。 */
$config->jenshin->defaultProjectModel = 'scrum';
$config->jenshin->skipCreateGuide    = true;
$config->jenshin->lockProjectModel   = true;

/* 项目详情二级菜单中隐藏的项。超级管理员直链仍可用于排障。 */
$config->jenshin->hiddenProjectMenus = array('qa', 'build', 'release');

if(!function_exists('jxHideProjectMenus'))
{
    /**
     * 从各项目模型导航中移除指定二级菜单。
     */
    function jxHideProjectMenus($lang, array $menuKeys): void
    {
        if(empty($lang) || empty($menuKeys)) return;

        $langKeys = array('project', 'scrum', 'waterfall', 'kanbanProject', 'agileplus', 'waterfallplus', 'ipd');
        foreach($langKeys as $key)
        {
            if(!isset($lang->$key->menu)) continue;
            foreach($menuKeys as $menuKey)
            {
                if(isset($lang->$key->menu->$menuKey)) unset($lang->$key->menu->$menuKey);
            }
        }

        if(empty($lang->project->noMultiple)) return;
        foreach(array('scrum', 'kanban', 'waterfall') as $key)
        {
            if(!isset($lang->project->noMultiple->$key->menu)) continue;
            foreach($menuKeys as $menuKey)
            {
                if(isset($lang->project->noMultiple->$key->menu->$menuKey)) unset($lang->project->noMultiple->$key->menu->$menuKey);
            }
        }
    }
}

/* 项目设置：团队 / 白名单 / 干系人 / 项目权限。不受项目管理员身份放行。 */
$config->jenshin->projectSettingsPrivs = array(
    'stakeholder' => true,
    'project'     => array(
        'team' => 1, 'managemembers' => 1, 'unlinkmember' => 1,
        'group' => 1, 'creategroup' => 1, 'managepriv' => 1, 'managegroupmember' => 1, 'copygroup' => 1, 'editgroup' => 1,
        'whitelist' => 1, 'addwhitelist' => 1, 'unbindwhitelist' => 1
    )
);

if(!function_exists('jxNeedGroupPrivForProjectSettings'))
{
    /**
     * 这些方法只认权限分组勾选，项目管理员 / 项目负责人不能绕过。
     */
    function jxNeedGroupPrivForProjectSettings($module, $method): bool
    {
        global $config;
        if(empty($config->jenshin->projectSettingsPrivs)) return false;
        $map    = $config->jenshin->projectSettingsPrivs;
        $module = strtolower((string)$module);
        $method = strtolower((string)$method);
        if($module === 'stakeholder' && !empty($map['stakeholder'])) return true;
        return $module === 'project' && !empty($map['project'][$method]);
    }
}

/* 工作台「SSH密钥」。false 隐藏菜单并拦截入口，改 true 即可恢复。 */
$config->jenshin->enableSSH = false;
if(empty($config->jenshin->enableSSH) && !empty($config->logonMethods))
{
    $config->logonMethods = array_values(array_diff($config->logonMethods, array('my.ssh', 'my.createssh', 'my.editssh', 'my.deletessh')));
}

/* 产品注册 / 市场准入 / 推广入院 / 旧数据看板。false 不注册一级菜单，模块代码保留，改 true 即可恢复。 */
$config->jenshin->legacyBizModules = array('jxregistration', 'jxmarketaccess', 'jxadmission', 'jxdashboard');
$config->jenshin->enableLegacyBizMenus = false;

$config->jenshin->bizTypes = array('registration', 'marketaccess', 'admission', 'quality', 'ip', 'supply', 'brand', 'it');
$config->jenshin->healthList = array('green', 'yellow', 'red');
$config->jenshin->stageStatus = array('wait', 'doing', 'submitted', 'approved', 'rejected', 'done');
$config->jenshin->certWarnDays = 90;
$config->jenshin->windowWarnDays = 14;

$config->openMethods[] = 'jxsso.login';

if(!isset($config->jenshin->sso)) $config->jenshin->sso = new stdclass();
$config->jenshin->sso->enabled           = true;
$config->jenshin->sso->secret            = '';
$config->jenshin->sso->issuer            = 'boke-info-pro';
$config->jenshin->sso->audience          = 'jenshin-pm-system';
$config->jenshin->sso->clockSkewSeconds  = 30;
$config->jenshin->sso->homeLink          = '';
if(!empty($config->jenshinSsoSecret)) $config->jenshin->sso->secret = (string)$config->jenshinSsoSecret;
if(isset($config->jenshinSsoEnabled)) $config->jenshin->sso->enabled = (bool)$config->jenshinSsoEnabled;
if(!empty($config->jenshinSsoHomeLink)) $config->jenshin->sso->homeLink = (string)$config->jenshinSsoHomeLink;

if(isset($filter))
{
    if(!isset($filter->jxsso)) $filter->jxsso = new stdclass();
    if(!isset($filter->jxsso->login)) $filter->jxsso->login = new stdclass();
    if(!isset($filter->jxsso->login->get)) $filter->jxsso->login->get = array();
    $filter->jxsso->login->get['token'] = 'reg::any';
}

if(!defined('TABLE_JX_PRODUCT'))       define('TABLE_JX_PRODUCT',       '`' . $config->db->prefix . 'jx_product`');
if(!defined('TABLE_JX_PROJECT'))       define('TABLE_JX_PROJECT',       '`' . $config->db->prefix . 'jx_project`');
if(!defined('TABLE_JX_TEMPLATE'))      define('TABLE_JX_TEMPLATE',      '`' . $config->db->prefix . 'jx_template`');
if(!defined('TABLE_JX_TEMPLATESTAGE')) define('TABLE_JX_TEMPLATESTAGE', '`' . $config->db->prefix . 'jx_templatestage`');
if(!defined('TABLE_JX_TEMPLATECHECK')) define('TABLE_JX_TEMPLATECHECK', '`' . $config->db->prefix . 'jx_templatecheck`');
if(!defined('TABLE_JX_STAGE'))         define('TABLE_JX_STAGE',         '`' . $config->db->prefix . 'jx_stage`');
if(!defined('TABLE_JX_CHECK'))         define('TABLE_JX_CHECK',         '`' . $config->db->prefix . 'jx_check`');
if(!defined('TABLE_JX_APPROVAL'))      define('TABLE_JX_APPROVAL',      '`' . $config->db->prefix . 'jx_approval`');
if(!defined('TABLE_JX_COST'))          define('TABLE_JX_COST',          '`' . $config->db->prefix . 'jx_cost`');
if(!defined('TABLE_JX_REGISTRATION'))  define('TABLE_JX_REGISTRATION',  '`' . $config->db->prefix . 'jx_registration`');
if(!defined('TABLE_JX_MARKETACCESS'))  define('TABLE_JX_MARKETACCESS',  '`' . $config->db->prefix . 'jx_marketaccess`');
if(!defined('TABLE_JX_HOSPITAL'))      define('TABLE_JX_HOSPITAL',      '`' . $config->db->prefix . 'jx_hospital`');
if(!defined('TABLE_JX_ADMISSION'))     define('TABLE_JX_ADMISSION',     '`' . $config->db->prefix . 'jx_admission`');
if(!defined('TABLE_JX_SCHEMA'))        define('TABLE_JX_SCHEMA',        '`' . $config->db->prefix . 'jx_schema`');

$config->objectTables['jxproduct']      = TABLE_JX_PRODUCT;
$config->objectTables['jxproject']      = TABLE_JX_PROJECT;
$config->objectTables['jxregistration'] = TABLE_JX_REGISTRATION;
$config->objectTables['jxmarketaccess'] = TABLE_JX_MARKETACCESS;
$config->objectTables['jxadmission']    = TABLE_JX_ADMISSION;
$config->objectTables['jxhospital']     = TABLE_JX_HOSPITAL;
$config->objectTables['jxstage']        = TABLE_JX_STAGE;
$config->objectTables['jxcost']         = TABLE_JX_COST;
$config->objectTables['jxdashboard']    = TABLE_JX_PROJECT;
$config->objectTables['jxboard']        = TABLE_PROJECT;
