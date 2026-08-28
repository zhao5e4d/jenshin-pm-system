<?php
/**
 * Treat "inited but no rows" as uninitialized so project 项目看板 can recover.
 */
public function getBlockInitStatus(string $dashboard): bool
{
    if(!$dashboard) return false;
    if(!parent::getBlockInitStatus($dashboard)) return false;

    $blockID = $this->dao->select('id')->from(TABLE_BLOCK)
        ->where('account')->eq($this->app->user->account)
        ->andWhere('dashboard')->eq($dashboard)
        ->andWhere('vision')->eq($this->config->vision)
        ->limit(1)
        ->fetch('id');

    return !empty($blockID);
}
