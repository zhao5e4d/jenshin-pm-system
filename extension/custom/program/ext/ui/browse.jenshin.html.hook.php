<?php
namespace zin;

global $config;

$model = !empty($config->jenshin->defaultProjectModel) ? $config->jenshin->defaultProjectModel : 'scrum';
$link  = createLink('project', 'create', "model={$model}");

query('.create-project-btn')->prop(array(
    'url'         => $link,
    'data-toggle' => null
));
