<?php
namespace zin;

global $config;
if(empty($config->jenshin->enableHelp))
{
    query('#featureBar li.nav-item.mr-3')->remove();
}
