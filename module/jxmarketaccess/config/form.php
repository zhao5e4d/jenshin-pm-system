<?php
declare(strict_types=1);
$config->jxmarketaccess->form = new stdclass();
$fields = array(
    'product'      => array('required' => true,  'type' => 'int',    'default' => 0),
    'name'         => array('required' => true,  'type' => 'string', 'filter' => 'trim'),
    'code'         => array('required' => false, 'type' => 'string', 'default' => ''),
    'type'         => array('required' => true,  'type' => 'string', 'default' => 'listing'),
    'region'       => array('required' => false, 'type' => 'string', 'default' => ''),
    'platform'     => array('required' => false, 'type' => 'string', 'default' => ''),
    'package'      => array('required' => false, 'type' => 'string', 'default' => ''),
    'windowBegin'  => array('required' => false, 'type' => 'date',   'default' => null),
    'windowEnd'    => array('required' => false, 'type' => 'date',   'default' => null),
    'quote'        => array('required' => false, 'type' => 'float',  'default' => 0),
    'result'       => array('required' => false, 'type' => 'string', 'default' => ''),
    'agreementNo'  => array('required' => false, 'type' => 'string', 'default' => ''),
    'fulfillBegin' => array('required' => false, 'type' => 'date',   'default' => null),
    'fulfillEnd'   => array('required' => false, 'type' => 'date',   'default' => null),
    'requireCert'  => array('required' => false, 'type' => 'int',    'default' => 1),
    'dependsOn'    => array('required' => false, 'type' => 'int',    'default' => 0),
    'owner'        => array('required' => false, 'type' => 'string', 'default' => ''),
    'leadDept'     => array('required' => false, 'type' => 'string', 'default' => ''),
    'supportDepts' => array('required' => false, 'type' => 'array',  'default' => array()),
    'begin'        => array('required' => true,  'type' => 'date'),
    'end'          => array('required' => false, 'type' => 'date',   'default' => null),
    'budget'       => array('required' => false, 'type' => 'float',  'default' => 0),
    'desc'         => array('required' => false, 'type' => 'string', 'default' => '')
);
$config->jxmarketaccess->form->create = $fields;
$config->jxmarketaccess->form->edit   = $fields;
