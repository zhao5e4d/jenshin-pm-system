<?php
/**
 * After ZenTao builds project menus, drop 项目设置 items the group did not grant.
 */
public function setMenu(int $projectID): int|bool
{
    $result = parent::setMenu($projectID);
    $this->jxHideDesignMenu();
    $this->jxFilterProjectSettingsMenu();
    return $result;
}

public function jxHideDesignMenu(): void
{
    $keys = array('project', 'scrum', 'waterfall', 'kanbanProject', 'agileplus', 'waterfallplus', 'ipd');
    foreach($keys as $key)
    {
        if(isset($this->lang->$key->menu->design)) unset($this->lang->$key->menu->design);
    }
    if(isset($this->lang->project->noMultiple))
    {
        foreach(array('scrum', 'kanban', 'waterfall') as $key)
        {
            if(isset($this->lang->project->noMultiple->$key->menu->design)) unset($this->lang->project->noMultiple->$key->menu->design);
        }
    }
}

public function buildActionList(object $project): array
{
    $actions = parent::buildActionList($project);
    $filtered = array();
    foreach($actions as $action)
    {
        $name = is_array($action) ? zget($action, 'name', '') : $action;
        if($name === 'group' && !common::hasPriv('project', 'team')) continue;
        if($name === 'perm' && !common::hasPriv('project', 'group')) continue;
        if($name === 'whitelist' && !common::hasPriv('project', 'whitelist')) continue;
        $filtered[] = $action;
    }
    return $filtered;
}

public function jxFilterProjectSettingsMenu(): void
{
    $items = array(
        'members'     => array('project', 'team'),
        'whitelist'   => array('project', 'whitelist'),
        'stakeholder' => array('stakeholder', 'browse'),
        'group'       => array('project', 'group')
    );

    $targets = array();
    foreach(array('project', 'scrum', 'waterfall', 'kanbanProject', 'agileplus', 'waterfallplus') as $key)
    {
        if(isset($this->lang->$key->menu->settings['subMenu']) && is_object($this->lang->$key->menu->settings['subMenu']))
        {
            $targets[] = $this->lang->$key->menu->settings['subMenu'];
        }
        if(isset($this->lang->project->noMultiple->$key->menu->settings['subMenu']) && is_object($this->lang->project->noMultiple->$key->menu->settings['subMenu']))
        {
            $targets[] = $this->lang->project->noMultiple->$key->menu->settings['subMenu'];
        }
    }

    foreach($targets as $subMenu)
    {
        foreach($items as $name => $priv)
        {
            if(!isset($subMenu->$name)) continue;
            if(!common::hasPriv($priv[0], $priv[1])) unset($subMenu->$name);
        }
    }
}
