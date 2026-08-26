<?php
global $lang, $app;
if(empty($lang->jxcore->statusList ?? null)) $app->loadLang('jxcore');
$config->jxregistration->dtable = new stdclass();
$config->jxregistration->dtable->fieldList['id']['name']  = 'id';
$config->jxregistration->dtable->fieldList['id']['title'] = $lang->idAB;
$config->jxregistration->dtable->fieldList['id']['type']  = 'id';

$config->jxregistration->dtable->fieldList['name']['name'] = 'name';
$config->jxregistration->dtable->fieldList['name']['title'] = $lang->jxregistration->name;
$config->jxregistration->dtable->fieldList['name']['type']  = 'title';
$config->jxregistration->dtable->fieldList['name']['link']  = array('module' => 'jxregistration', 'method' => 'view', 'params' => 'id={id}');
$config->jxregistration->dtable->fieldList['name']['flex']  = 1;

$config->jxregistration->dtable->fieldList['code']['name']  = 'code';
$config->jxregistration->dtable->fieldList['code']['title'] = $lang->jxregistration->code;
$config->jxregistration->dtable->fieldList['code']['type']  = 'text';

$config->jxregistration->dtable->fieldList['productName']['name']  = 'productName';
$config->jxregistration->dtable->fieldList['productName']['title'] = $lang->jxregistration->product;
$config->jxregistration->dtable->fieldList['productName']['type']  = 'text';

$config->jxregistration->dtable->fieldList['type']['name']  = 'type';
$config->jxregistration->dtable->fieldList['type']['title'] = $lang->jxregistration->type;
$config->jxregistration->dtable->fieldList['type']['type']  = 'text';
$config->jxregistration->dtable->fieldList['type']['map']   = $lang->jxregistration->typeList;

$config->jxregistration->dtable->fieldList['ownerName']['name']  = 'ownerName';
$config->jxregistration->dtable->fieldList['ownerName']['title'] = $lang->jxregistration->owner;
$config->jxregistration->dtable->fieldList['ownerName']['type']  = 'text';

$config->jxregistration->dtable->fieldList['status']['name']  = 'status';
$config->jxregistration->dtable->fieldList['status']['title'] = $lang->jxregistration->status;
$config->jxregistration->dtable->fieldList['status']['type']  = 'status';
$config->jxregistration->dtable->fieldList['status']['statusMap'] = $lang->jxcore->statusList;

$config->jxregistration->dtable->fieldList['progress']['name']  = 'progress';
$config->jxregistration->dtable->fieldList['progress']['title'] = $lang->jxcore->progress;
$config->jxregistration->dtable->fieldList['progress']['type']  = 'progress';

$config->jxregistration->dtable->fieldList['health']['name']  = 'health';
$config->jxregistration->dtable->fieldList['health']['title'] = $lang->jxcore->health;
$config->jxregistration->dtable->fieldList['health']['type']  = 'text';
$config->jxregistration->dtable->fieldList['health']['map']   = $lang->jxcore->healthList;

$config->jxregistration->dtable->fieldList['end']['name']  = 'end';
$config->jxregistration->dtable->fieldList['end']['title'] = $lang->jxregistration->end;
$config->jxregistration->dtable->fieldList['end']['type']  = 'date';
