<?php
namespace zin;

global $config;

$product = data('product');
$branchID = (int)data('branchID');
$model    = !empty($config->jenshin->defaultProjectModel) ? $config->jenshin->defaultProjectModel : 'scrum';
$programID = !empty($product->program) ? (int)$product->program : 0;
$productID = !empty($product->id) ? (int)$product->id : 0;
$extra     = "productID={$productID},branchID={$branchID}";
$link      = createLink('project', 'create', "model={$model}&programID={$programID}&copyProjectID=0&extra={$extra}");

query('.create-project-btn')->prop(array(
    'url'         => $link,
    'data-toggle' => null,
    'data-type'   => null
));
