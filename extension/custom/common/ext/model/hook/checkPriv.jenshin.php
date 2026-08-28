<?php
/**
 * Deny software R&D modules for non-admin users even via direct URL.
 */
commonModel::$userPrivs = array();

$jxModule = $this->app->getModuleName();
$jxMethod = strtolower((string)$this->app->getMethodName());

if(!empty($this->config->jenshin->blockedModules))
{
    if(in_array($jxModule, $this->config->jenshin->blockedModules, true))
    {
        $account   = !empty($this->app->user->account) ? $this->app->user->account : '';
        $isJxAdmin = !empty($this->app->user->admin) || ($account && !empty($this->app->company->admins) && strpos((string)$this->app->company->admins, ",{$account},") !== false);
        if($account && !$isJxAdmin)
        {
            $this->deny($jxModule, $this->app->getMethodName(), false);
        }
    }
}

if(empty($this->config->jenshin->enableSSH) && $jxModule === 'my' && in_array($jxMethod, array('ssh', 'createssh', 'editssh', 'deletessh'), true))
{
    $this->deny($jxModule, $this->app->getMethodName(), false);
}

/* Seed/import often leaves zt_userview.projects empty; project index then 403s or renders a blank 项目看板. */
if(!empty($this->app->user->account) && isset($this->app->user->view) && (string)$this->app->user->view->projects === '')
{
    static $jxViewRefreshed = false;
    if(!$jxViewRefreshed)
    {
        $jxViewRefreshed = true;
        try
        {
            $this->loadModel('user')->computeUserView($this->app->user->account, true);
            $fresh = $this->dao->select('*')->from(TABLE_USERVIEW)->where('account')->eq($this->app->user->account)->fetch();
            if($fresh)
            {
                $this->app->user->view->programs = $fresh->programs;
                $this->app->user->view->products = $fresh->products;
                $this->app->user->view->projects = $fresh->projects;
                $this->app->user->view->sprints  = $fresh->sprints;
            }
        }
        catch(Throwable $e) { }
    }
}

/* 未勾选「项目设置」时，拦截团队 / 白名单 / 干系人 / 项目权限，避免项目管理员旁路。 */
if(!empty($this->app->user->account) && empty($this->app->user->admin)
    && function_exists('jxNeedGroupPrivForProjectSettings')
    && jxNeedGroupPrivForProjectSettings($jxModule, $jxMethod))
{
    $jxRights = isset($this->app->user->rights['rights']) ? $this->app->user->rights['rights'] : array();
    if(empty($jxRights[strtolower($jxModule)][strtolower($jxMethod)]))
    {
        $this->deny($jxModule, $this->app->getMethodName(), false);
    }
}
