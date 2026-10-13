<?php
/**
 * 待办总数沿用工作台筛选条件，排除挂起任务，不受区块条数限制。
 */
public function getJxPendingTodoCount(): int
{
    $account = (string)$this->app->user->account;
    if($account === '') return 0;
    $suspendedTasks = $this->loadModel('task')->getUserSuspendedTasks($account);
    return (int)$this->dao->select('COUNT(*) AS total')->from(TABLE_TODO)
        ->where('deleted')->eq(0)->andWhere('assignedTo')->eq($account)
        ->andWhere('cycle')->eq(0)->andWhere('status')->eq('wait')
        ->andWhere('vision')->eq($this->config->vision)
        ->beginIF(!empty($suspendedTasks))->andWhere('(type')->ne('task')
            ->orWhere('objectID')->notin(array_keys($suspendedTasks))->markRight(1)->fi()
        ->fetch('total');
}

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
