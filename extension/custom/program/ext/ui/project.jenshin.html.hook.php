<?php
namespace zin;

global $config;

$programID = (int)data('programID');
$model     = !empty($config->jenshin->defaultProjectModel) ? $config->jenshin->defaultProjectModel : 'scrum';
$link      = createLink('project', 'create', "model={$model}&programID={$programID}");

query('.btn.primary')->prop(array(
    'url'         => $link,
    'data-toggle' => null
));
