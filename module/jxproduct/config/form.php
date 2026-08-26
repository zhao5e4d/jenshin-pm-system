<?php
declare(strict_types=1);
$config->jxproduct->form = new stdclass();
$fields = array(
    'model'        => array('required' => false, 'type' => 'string', 'default' => ''),
    'category'     => array('required' => false, 'type' => 'string', 'default' => ''),
    'line'         => array('required' => false, 'type' => 'string', 'default' => ''),
    'certNo'       => array('required' => false, 'type' => 'string', 'default' => ''),
    'certValidTo'  => array('required' => false, 'type' => 'date',   'default' => null),
    'specs'        => array('required' => false, 'type' => 'string', 'default' => ''),
    'udi'          => array('required' => false, 'type' => 'string', 'default' => ''),
    'manufacturer' => array('required' => false, 'type' => 'string', 'default' => ''),
    'patents'      => array('required' => false, 'type' => 'string', 'default' => ''),
    'tenderCode'   => array('required' => false, 'type' => 'string', 'default' => ''),
    'desc'         => array('required' => false, 'type' => 'string', 'default' => ''),
    'name'         => array('required' => true,  'type' => 'string', 'filter' => 'trim')
);
$config->jxproduct->form->create = $fields;
$config->jxproduct->form->edit   = $fields;
