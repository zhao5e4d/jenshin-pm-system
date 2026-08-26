<?php
declare(strict_types=1);
$config->jxadmission->form = new stdclass();
$fields = array(
    'product'        => array('required' => true,  'type' => 'int',    'default' => 0),
    'hospital'       => array('required' => false, 'type' => 'int',    'default' => 0),
    'name'           => array('required' => true,  'type' => 'string', 'filter' => 'trim'),
    'code'           => array('required' => false, 'type' => 'string', 'default' => ''),
    'path'           => array('required' => false, 'type' => 'string', 'default' => ''),
    'department'     => array('required' => false, 'type' => 'string', 'default' => ''),
    'pharmacyDate'   => array('required' => false, 'type' => 'date',   'default' => null),
    'firstOrderDate' => array('required' => false, 'type' => 'date',   'default' => null),
    'volume'         => array('required' => false, 'type' => 'float',  'default' => 0),
    'repurchase'     => array('required' => false, 'type' => 'string', 'default' => ''),
    'owner'          => array('required' => false, 'type' => 'string', 'default' => ''),
    'leadDept'       => array('required' => false, 'type' => 'string', 'default' => ''),
    'supportDepts'   => array('required' => false, 'type' => 'array',  'default' => array()),
    'begin'          => array('required' => true,  'type' => 'date'),
    'end'            => array('required' => false, 'type' => 'date',   'default' => null),
    'budget'         => array('required' => false, 'type' => 'float',  'default' => 0),
    'desc'           => array('required' => false, 'type' => 'string', 'default' => '')
);
$config->jxadmission->form->create = $fields;
$config->jxadmission->form->edit   = $fields;
