<?php
if(function_exists('jxHideBugKanban') && jxHideBugKanban())
{
    unset($lang->kanban->type['bug']);
    unset($lang->kanban->group->bug);
    unset($lang->kanban->laneTypeList['bug']);
}
