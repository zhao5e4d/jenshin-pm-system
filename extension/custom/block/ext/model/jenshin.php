<?php
/**
 * Treat "inited but no rows" as uninitialized so project 项目看板 can recover.
 */
public function getByID(int $blockID): object|false
{
    $block = parent::getByID($blockID);
    if($block) $this->jxRewriteExecutionBlockTitle($block);
    return $block;
}

public function jxRewriteExecutionBlockTitle(object $block): void
{
    if(!in_array($block->code ?? '', array('scrumlist', 'sprint'), true) || empty($block->title)) return;
    $block->title = str_replace(array('任务执行', '迭代', '冲刺'), array($this->lang->executionCommon, $this->lang->executionCommon, $this->lang->executionCommon), $block->title);
}

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
