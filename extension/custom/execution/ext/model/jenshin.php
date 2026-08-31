<?php
/**
 * After ZenTao builds execution menus, drop 测试 / 构建 / 发布 for medical PM.
 */
public function setMenu(int $executionID)
{
    $result = parent::setMenu($executionID);
    $this->jxHideExecutionMenus();
    return $result;
}

public function getExecutionFeatures(object $execution): array
{
    $features = parent::getExecutionFeatures($execution);
    if(function_exists('jxHideBugKanban') && jxHideBugKanban()) $features['qa'] = false;
    return $features;
}

public function jxHideExecutionMenus(): void
{
    $menuKeys = !empty($this->config->jenshin->hiddenProjectMenus) && is_array($this->config->jenshin->hiddenProjectMenus)
        ? $this->config->jenshin->hiddenProjectMenus
        : array('qa', 'build', 'release');

    if(function_exists('jxHideProjectMenus'))
    {
        jxHideProjectMenus($this->lang, $menuKeys);
        return;
    }

    foreach($menuKeys as $menuKey)
    {
        if(isset($this->lang->execution->menu->$menuKey)) unset($this->lang->execution->menu->$menuKey);
    }
    if(isset($this->lang->execution->dividerMenu) && function_exists('jxStripDividerMenus'))
    {
        jxStripDividerMenus($this->lang->execution->dividerMenu, $menuKeys);
    }
}
