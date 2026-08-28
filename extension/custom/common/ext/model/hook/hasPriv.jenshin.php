<?php
/**
 * Apache 会跨请求复用 commonModel::$userPrivs，缓存键不含账号。
 * define() 只在进程内生效一次，后续请求不会清缓存。
 * 按「请求 + 账号」重置，避免访客/他人的 false 污染本请求。
 */
static $jxPrivToken = null;
global $app;
$jxAccount = (isset($app->user) && !empty($app->user->account)) ? (string)$app->user->account : '';
$jxToken   = (isset($_SERVER['REQUEST_TIME_FLOAT']) ? (string)$_SERVER['REQUEST_TIME_FLOAT'] : '') . '|' . $jxAccount;
if($jxPrivToken !== $jxToken)
{
    $jxPrivToken = $jxToken;
    commonModel::$userPrivs = array();
}

/**
 * 项目设置（团队 / 白名单 / 干系人 / 项目权限）只认权限分组里勾选的方法。
 * 注意：核心多处调用 commonModel::hasPriv()，本 hook 只覆盖 extcommonModel::hasPriv。
 * 真正拦截在 getUserPriv（跳过项目管理员旁路）和 checkPriv hook。
 */
if(function_exists('jxNeedGroupPrivForProjectSettings') && jxNeedGroupPrivForProjectSettings($module, $method))
{
    global $app;
    if(!empty($app->user->account) && empty($app->user->admin) && !commonModel::isTutorialMode())
    {
        $jxRights = isset($app->user->rights['rights']) ? $app->user->rights['rights'] : array();
        if(empty($jxRights[strtolower((string)$module)][strtolower((string)$method)])) return false;
    }
}
