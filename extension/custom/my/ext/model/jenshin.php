<?php
/**
 * 工作台审批列表不混入已裁剪的用例 / Bug。
 */
public function getReviewingList(string $browseType, string $orderBy = 'time_desc', ?object $pager = null): array
{
    if($browseType === 'testcase' || $browseType === 'bug')
    {
        if($pager)
        {
            $pager->setRecTotal(0);
            $pager->setPageTotal();
        }
        return array();
    }

    $reviewList = parent::getReviewingList($browseType, $orderBy, null);
    $blockedTypes = array('testcase', 'bug', 'testtask', 'case');
    if(!empty($this->config->jenshin->blockedModules) && is_array($this->config->jenshin->blockedModules))
    {
        $blockedTypes = array_values(array_unique(array_merge($blockedTypes, $this->config->jenshin->blockedModules)));
    }

    $reviewList = array_values(array_filter($reviewList, function($item) use ($blockedTypes)
    {
        $type = isset($item->type) ? (string)$item->type : '';
        return $type !== '' && !in_array($type, $blockedTypes, true);
    }));

    if($pager)
    {
        $pager->setRecTotal(count($reviewList));
        $pager->setPageTotal();
        $pager->setPageID($pager->pageID);
        if($reviewList)
        {
            $chunks      = array_chunk($reviewList, max(1, (int)$pager->recPerPage));
            $pageIndex   = max(0, (int)$pager->pageID - 1);
            $reviewList  = $chunks[$pageIndex] ?? array();
        }
    }

    return $reviewList;
}
