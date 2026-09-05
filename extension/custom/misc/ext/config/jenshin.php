<?php
/**
 * 健忻 1.0 首页引导：关掉禅道 20.0 / AI Skill 弹窗，只保留本系统介绍。
 *
 * 文案和配图在 www/static/svg/jenshin/（cn_ / en_ 各 4 页）。
 * 生效依赖 model hook：ext/model/hook/getPendingFeatureNotices.jenshin.php。
 * 只在工作台（my/index）弹出。打开不会记已读；只有点「立即体验」才写入 showUpgradeGuide（固定写 jx11）。
 * 点其它一级菜单会关掉弹窗，但不会记已读。某账号再弹一次：把该账号 showUpgradeGuide 中的 jx11 去掉。
 */
$config->featureNotice = array();
$config->featureNotice[] = array(
    'code'   => 'jx11',
    'images' => array(
        'static/svg/jenshin/{lang}_guide1_1_1.svg',
        'static/svg/jenshin/{lang}_guide2_1_1.svg',
        'static/svg/jenshin/{lang}_guide3_1_1.svg',
        'static/svg/jenshin/{lang}_guide4_1_1.svg'
    )
);
