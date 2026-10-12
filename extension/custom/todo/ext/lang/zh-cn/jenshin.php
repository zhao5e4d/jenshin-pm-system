<?php
/**
 * 任务协同发给协同人的待办，在待办列表里显示成「任务协同」。
 * 手工新建待办时不提供这个类型，它只能由协同单生成。
 * 注意：后台自定义过待办类型时 zt_lang 会整体覆盖 typeList，
 * deploy/jenshin-migrate.php 里同步补一条 jxcollab 记录。
 */
global $app;

$app->loadLang('jxcollab');

$lang->todo->typeList['jxcollab'] = $lang->jxcollab->common;
$lang->todo->fromList['jxcollab'] = $lang->jxcollab->collab;

if($app->rawModule == 'todo' && in_array($app->rawMethod, array('create', 'batchcreate'))) unset($lang->todo->typeList['jxcollab']);
