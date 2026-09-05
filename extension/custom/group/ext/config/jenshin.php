<?php
if(!isset($config->group->subset))  $config->group->subset  = new stdclass();
if(!isset($config->group->package)) $config->group->package = new stdclass();

$config->group->subset->jxboard = new stdclass();
$config->group->subset->jxboard->order = 100;
$config->group->subset->jxboard->nav   = 'jxboard';

$config->group->package->browseJxboard = new stdclass();
$config->group->package->browseJxboard->order  = 5;
$config->group->package->browseJxboard->subset = 'jxboard';
$config->group->package->browseJxboard->privs  = array();
$jxBoardOrder = 0;
foreach(array('index', 'overview', 'dept', 'portfolio', 'meeting', 'screen') as $jxMethod)
{
    $config->group->package->browseJxboard->privs['jxboard-' . $jxMethod] = array(
        'edition'   => 'open,biz,max,ipd',
        'vision'    => 'rnd,lite,or',
        'order'     => $jxBoardOrder,
        'depend'    => array(),
        'recommend' => array(),
    );
    $jxBoardOrder += 5;
}

/* 菜单已关的测试 / 构建 / 代码 / 发布权限包不再出现在分组页。 */
foreach(array('projectqa', 'executionqa', 'executionbuild', 'repo', 'release', 'projectrelease') as $jxHideSubset)
{
    if(isset($config->group->subset->$jxHideSubset)) unset($config->group->subset->$jxHideSubset);
}

foreach(array(
    'browseRelease', 'manageRelease', 'importRelease',
    'deleteRelease', 'releaseNotify', 'application',
    'browseProjectRelease', 'manageProjectRelease', 'projectReleaseNotify'
) as $jxHidePackage)
{
    if(isset($config->group->package->$jxHidePackage)) unset($config->group->package->$jxHidePackage);
}

/*
 * 禅道把 product-browse（需求列表页）归在「浏览需求」权限包，
 * 同时又让「浏览产品 / 白名单」强制依赖它。保存时 processDepends 会把
 * product-browse 写回去，表现为取消「需求」后「浏览需求」又被勾上。
 * 健忻查看产品详情、关联项目、白名单不依赖需求列表，去掉该强制依赖。
 */
$jxRelaxProductBrowseDepends = array(
    'browseProduct'     => array('product-view', 'product-roadmap', 'product-track', 'product-dynamic', 'product-project'),
    'productWhitelist'  => array('product-whitelist'),
    'browseProductPlan' => array('productplan-browse'),
    'browseRelease'     => array('release-browse'),
);
foreach($jxRelaxProductBrowseDepends as $packageName => $privCodes)
{
    if(empty($config->group->package->$packageName->privs)) continue;
    foreach($privCodes as $privCode)
    {
        if(empty($config->group->package->$packageName->privs[$privCode]['depend'])) continue;
        $config->group->package->$packageName->privs[$privCode]['depend'] = array_values(array_diff(
            $config->group->package->$packageName->privs[$privCode]['depend'],
            array('product-browse')
        ));
    }
}
