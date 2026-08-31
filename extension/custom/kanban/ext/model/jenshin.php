<?php
/**
 * Drop Bug lanes from execution / RD kanban. Existing DB lanes stay, they are just not rendered.
 */
public function getExecutionKanban(int $executionID, string $browseType = 'all', string $groupBy = 'default', string $searchValue = '', string $orderBy = 'id_asc'): array
{
    if(function_exists('jxHideBugKanban') && jxHideBugKanban())
    {
        if($browseType == 'bug')
        {
            if($groupBy != 'default' && $groupBy != '') return array(array(), array());
            $browseType = 'all';
        }
    }

    $result = parent::getExecutionKanban($executionID, $browseType, $groupBy, $searchValue, $orderBy);
    if(!function_exists('jxHideBugKanban') || !jxHideBugKanban()) return $result;
    if(empty($result) || !isset($result[0]) || !is_array($result[0])) return $result;

    $result[0] = $this->jxFilterBugKanbanGroups($result[0]);
    return $result;
}

public function getRDKanban(int $executionID, string $browseType = 'all', string $orderBy = 'id_desc', int $regionID = 0, string $groupBy = 'default', string $searchValue = '')
{
    if(function_exists('jxHideBugKanban') && jxHideBugKanban() && $browseType == 'bug') $browseType = 'all';
    return parent::getRDKanban($executionID, $browseType, $orderBy, $regionID, $groupBy, $searchValue);
}

public function getLaneGroupByRegions(array $regions, string $browseType = 'all'): array
{
    if(function_exists('jxHideBugKanban') && jxHideBugKanban() && $browseType == 'bug') $browseType = 'all';
    $laneGroup = parent::getLaneGroupByRegions($regions, $browseType);
    if(!function_exists('jxHideBugKanban') || !jxHideBugKanban()) return $laneGroup;

    foreach($laneGroup as $groupID => $lanes)
    {
        $kept = array();
        foreach($lanes as $lane)
        {
            if(zget($lane, 'type', '') === 'bug') continue;
            $kept[] = $lane;
        }
        if($kept) $laneGroup[$groupID] = $kept;
        else unset($laneGroup[$groupID]);
    }
    return $laneGroup;
}

public function createExecutionLane(object $execution, string $type = 'all'): bool
{
    if(function_exists('jxHideBugKanban') && jxHideBugKanban() && $type == 'bug') return true;
    return parent::createExecutionLane($execution, $type);
}

public function jxFilterBugKanbanGroups(array $kanbanGroup): array
{
    $filtered = array();
    foreach($kanbanGroup as $group)
    {
        if(!is_array($group)) continue;
        $laneType = '';
        if(!empty($group['data']['lanes'][0]['type'])) $laneType = $group['data']['lanes'][0]['type'];
        elseif(!empty($group['data']['cols'][0]['group'])) $laneType = $group['data']['cols'][0]['group'];
        if($laneType === 'bug') continue;
        $filtered[] = $group;
    }
    return $filtered;
}
