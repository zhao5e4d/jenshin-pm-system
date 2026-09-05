<?php
$lang->execution->overdueTasks = 'Overdue Tasks';
unset($lang->execution->lifeTimeList['ops']);

global $config;
if(empty($config->jenshin->enableHelp)) $lang->execution->howToUpdateBurn = '';
