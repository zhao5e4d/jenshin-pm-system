<?php
helper::importControl('execution');
class myExecution extends execution
{
    /**
     * 研发看板不再提供 Bug 类型；旧直链 type=bug 落到综合看板。
     */
    public function kanban(int $executionID, string $browseType = 'all', string $orderBy = 'id_asc', string $groupBy = 'default')
    {
        if(function_exists('jxHideBugKanban') && jxHideBugKanban() && $browseType == 'bug') $browseType = 'all';
        parent::kanban($executionID, $browseType, $orderBy, $groupBy);
    }
}
