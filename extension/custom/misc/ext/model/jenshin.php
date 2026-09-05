<?php
/**
 * 欢迎弹窗只展示，不在打开时记已读。点「立即体验」再写 showUpgradeGuide。
 */
public function getFeatureNotices(): array
{
    $features = $this->getPendingFeatureNotices();
    if(empty($features)) return array();

    return $this->buildFeatureNoticePages($features);
}

/**
 * 把当前账号的欢迎弹窗标为已读。只写健忻引导代号，不依赖 $config->featureNotice
 *（misc/config.php 会把它重置成禅道 ui20/aiskill）。
 */
public function dismissJenshinFeatureNotice(): bool
{
    $account = $this->app->user->account ?? '';
    if($account === '' || $account === 'guest') return false;

    $code    = 'jx11';
    $jxNotice = $this->app->getExtensionRoot() . 'custom' . DS . 'misc' . DS . 'ext' . DS . 'config' . DS . 'jenshin.php';
    if(is_file($jxNotice))
    {
        global $config;
        include $jxNotice;
        if(!empty($config->featureNotice[0]['code'])) $code = $config->featureNotice[0]['code'];
        $this->config->featureNotice = $config->featureNotice;
    }

    $noticed = $this->config->global->showUpgradeGuide ?? '';
    if(strpos(",{$noticed},", ",{$code},") === false) $noticed = trim($noticed . ',' . $code, ',');

    return $this->loadModel('setting')->setItem("{$account}.common.global.showUpgradeGuide", $noticed);
}
