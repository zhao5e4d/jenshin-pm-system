<?php
/**
 * 健忻 1.0 首页引导：关掉禅道 20.0 / AI Skill 弹窗，只保留本系统介绍。
 *
 * 文案和配图在 www/static/svg/jenshin/（cn_ / en_ 各 4 页）。
 * 生效依赖 model hook：ext/model/hook/getPendingFeatureNotices.jenshin.php。
 * 某账号再弹一次：把 zt_config 里该账号 common.global.showUpgradeGuide 中的 jx10 去掉。
 */
$config->featureNotice = array();
$config->featureNotice[] = array(
    'code'   => 'jx10',
    'images' => array(
        'static/svg/jenshin/{lang}_guide1_1_0.svg',
        'static/svg/jenshin/{lang}_guide2_1_0.svg',
        'static/svg/jenshin/{lang}_guide3_1_0.svg',
        'static/svg/jenshin/{lang}_guide4_1_0.svg'
    )
);
