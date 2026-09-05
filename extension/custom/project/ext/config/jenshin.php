<?php
/**
 * 项目列表操作列：未勾选项目设置时不展示团队 / 权限 / 白名单按钮。
 */
if(!isset($config->project->dtable->fieldList['actions']['menu'])) return;

$jxMenu = $config->project->dtable->fieldList['actions']['menu'];
if(!is_array($jxMenu)) return;

if(class_exists('common'))
{
    if(!common::hasPriv('project', 'team')) $jxMenu = array_values(array_filter($jxMenu, function($item)
    {
        return $item !== 'group';
    }));
    if(!common::hasPriv('project', 'group')) $jxMenu = array_values(array_filter($jxMenu, function($item)
    {
        return $item !== 'perm';
    }));
    if(!common::hasPriv('project', 'whitelist'))
    {
        foreach($jxMenu as $jxKey => $jxItem)
        {
            if(!is_array($jxItem) || empty($jxItem['more']) || !is_array($jxItem['more'])) continue;
            $jxMenu[$jxKey]['more'] = array_values(array_diff($jxItem['more'], array('whitelist')));
        }
    }
}

$config->project->dtable->fieldList['actions']['menu'] = $jxMenu;
