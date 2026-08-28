<?php
if(!isset($config->group->subset))  $config->group->subset  = new stdclass();
if(!isset($config->group->package)) $config->group->package = new stdclass();

$config->group->subset->jxboard = new stdclass();
$config->group->subset->jxboard->order = 100;
$config->group->subset->jxboard->nav   = 'jxboard';

$config->group->package->browseJxboard = new stdclass();
$config->group->package->browseJxboard->order  = 5;
$config->group->package->browseJxboard->subset = 'jxboard';
$config->group->package->browseJxboard->privs  = array();
$jxBoardOrder = 0;
foreach(array('index', 'overview', 'dept', 'portfolio', 'meeting') as $jxMethod)
{
    $config->group->package->browseJxboard->privs['jxboard-' . $jxMethod] = array(
        'edition'   => 'open,biz,max,ipd',
        'vision'    => 'rnd,lite,or',
        'order'     => $jxBoardOrder,
        'depend'    => array(),
        'recommend' => array(),
    );
    $jxBoardOrder += 5;
}
