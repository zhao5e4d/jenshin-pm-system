<?php
helper::importControl('execution');
class myExecution extends execution
{
    /**
     * 综合看板不再提供 Bug 类型；旧直链 type=bug 落到综合看板。
     */
    public function taskKanban(int $executionID, string $browseType = 'all', string $orderBy = 'order_asc', string $groupBy = '')
    {
        if(function_exists('jxHideBugKanban') && jxHideBugKanban() && $browseType == 'bug') $browseType = 'all';
        parent::taskKanban($executionID, $browseType, $orderBy, $groupBy);
    }
}
