<?php
/**
 * 博科免登入口必须在顶层打开，否则会被包进 index iframe 并因未登录弹回登录页。
 */
$jxModule = $this->app->getModuleName();
$jxMethod = strtolower((string)$this->app->getMethodName());
if($jxModule === 'jxsso' && $jxMethod === 'login') return;
