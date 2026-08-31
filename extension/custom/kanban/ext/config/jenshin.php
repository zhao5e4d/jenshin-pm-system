<?php
if(function_exists('jxHideBugKanban') && jxHideBugKanban() && isset($config->kanban->default->bug))
{
    unset($config->kanban->default->bug);
}
