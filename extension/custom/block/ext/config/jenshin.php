<?php
if(isset($config->block->projectstatistic->items['bug'])) unset($config->block->projectstatistic->items['bug']);

/* 项目计划看板按超长区块铺满整行。 */
if(isset($config->block->size['waterfallproject']['waterfallgantt']))
{
    $config->block->size['waterfallproject']['waterfallgantt'] = array(3 => 8, 2 => 8, 1 => 8);
}
