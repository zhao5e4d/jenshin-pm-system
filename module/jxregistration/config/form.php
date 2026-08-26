<?php
declare(strict_types=1);
$config->jxregistration->form = new stdclass();
$fields = array(
    'product'        => array('required' => true,  'type' => 'int',    'default' => 0),
    'name'           => array('required' => true,  'type' => 'string', 'filter' => 'trim'),
    'code'           => array('required' => false, 'type' => 'string', 'default' => ''),
    'type'           => array('required' => true,  'type' => 'string', 'default' => 'first'),
    'category'       => array('required' => false, 'type' => 'string', 'default' => ''),
    'path'           => array('required' => false, 'type' => 'string', 'default' => ''),
    'acceptNo'       => array('required' => false, 'type' => 'string', 'default' => ''),
    'applyDate'      => array('required' => false, 'type' => 'date',   'default' => null),
    'supplementDate' => array('required' => false, 'type' => 'date',   'default' => null),
    'certDate'       => array('required' => false, 'type' => 'date',   'default' => null),
    'certNo'         => array('required' => false, 'type' => 'string', 'default' => ''),
    'certValidTo'    => array('required' => false, 'type' => 'date',   'default' => null),
    'owner'          => array('required' => false, 'type' => 'string', 'default' => ''),
    'leadDept'       => array('required' => false, 'type' => 'string', 'default' => ''),
    'supportDepts'   => array('required' => false, 'type' => 'array',  'default' => array()),
    'begin'          => array('required' => true,  'type' => 'date'),
    'end'            => array('required' => false, 'type' => 'date',   'default' => null),
    'budget'         => array('required' => false, 'type' => 'float',  'default' => 0),
    'desc'           => array('required' => false, 'type' => 'string', 'default' => '')
);
$config->jxregistration->form->create = $fields;
$config->jxregistration->form->edit   = $fields;
