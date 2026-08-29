<?php
global $config;

$lang->zentaoPMS = 'Jenshin Medical PM System';
$lang->logoImg  = 'jx-logo.png';
$lang->jenshinCopyright = '© Jenshin ' . date('Y');

if(empty($config->jenshin->enableSSH) && isset($lang->my->menu))
{
    unset($lang->my->menu->ssh, $lang->my->menuOrder[55]);
    if(!empty($lang->my->dividerMenu)) $lang->my->dividerMenu = str_replace(',ssh,', ',', $lang->my->dividerMenu);
}

if(function_exists('jxHideProjectMenus') && !empty($config->jenshin->hiddenProjectMenus))
{
    jxHideProjectMenus($lang, $config->jenshin->hiddenProjectMenus);
}
