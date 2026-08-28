<?php
/**
 * misc/config.php 会把 $config->featureNotice 重置成禅道 20.0 引导。
 * 本 hook 会被合并进 tmp/model，不能用 __DIR__。改弹窗请改 misc/ext/config/jenshin.php。
 */
global $config;
$jxNotice = $this->app->getExtensionRoot() . 'custom' . DS . 'misc' . DS . 'ext' . DS . 'config' . DS . 'jenshin.php';
if(is_file($jxNotice)) include $jxNotice;
if(!empty($config->featureNotice)) $this->config->featureNotice = $config->featureNotice;
