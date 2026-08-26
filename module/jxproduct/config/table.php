<?php
global $lang;
$config->jxproduct->dtable = new stdclass();
$config->jxproduct->dtable->fieldList['id']['name']     = 'id';
$config->jxproduct->dtable->fieldList['id']['title']    = $lang->idAB;
$config->jxproduct->dtable->fieldList['id']['type']     = 'id';
$config->jxproduct->dtable->fieldList['id']['sortType'] = true;

$config->jxproduct->dtable->fieldList['name']['name']  = 'name';
$config->jxproduct->dtable->fieldList['name']['title'] = $lang->jxproduct->name;
$config->jxproduct->dtable->fieldList['name']['type']  = 'title';
$config->jxproduct->dtable->fieldList['name']['link']  = array('module' => 'jxproduct', 'method' => 'view', 'params' => 'id={id}');
$config->jxproduct->dtable->fieldList['name']['flex']  = 1;

$config->jxproduct->dtable->fieldList['model']['name']  = 'model';
$config->jxproduct->dtable->fieldList['model']['title'] = $lang->jxproduct->model;
$config->jxproduct->dtable->fieldList['model']['type']  = 'text';

$config->jxproduct->dtable->fieldList['category']['name']  = 'category';
$config->jxproduct->dtable->fieldList['category']['title'] = $lang->jxproduct->category;
$config->jxproduct->dtable->fieldList['category']['type']  = 'text';

$config->jxproduct->dtable->fieldList['line']['name']  = 'line';
$config->jxproduct->dtable->fieldList['line']['title'] = $lang->jxproduct->line;
$config->jxproduct->dtable->fieldList['line']['type']  = 'text';

$config->jxproduct->dtable->fieldList['certNo']['name']  = 'certNo';
$config->jxproduct->dtable->fieldList['certNo']['title'] = $lang->jxproduct->certNo;
$config->jxproduct->dtable->fieldList['certNo']['type']  = 'text';

$config->jxproduct->dtable->fieldList['certValidTo']['name']  = 'certValidTo';
$config->jxproduct->dtable->fieldList['certValidTo']['title'] = $lang->jxproduct->certValidTo;
$config->jxproduct->dtable->fieldList['certValidTo']['type']  = 'date';

$config->jxproduct->dtable->fieldList['udi']['name']  = 'udi';
$config->jxproduct->dtable->fieldList['udi']['title'] = $lang->jxproduct->udi;
$config->jxproduct->dtable->fieldList['udi']['type']  = 'text';
