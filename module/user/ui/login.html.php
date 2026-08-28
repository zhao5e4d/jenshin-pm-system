<?php
declare(strict_types=1);
/**
 * Jenshin login page — sharp HTML chrome over figure-2 scene.
 */
namespace zin;

if(empty($config->notMd5Pwd)) h::import($config->webRoot . 'js/md5.js', 'js');

$resetLink     = empty($this->config->resetPWDByMail) ? inlink('reset') : inlink('forgetPassword');
$zentaoDirName = basename($this->app->getBasePath());
$clientLang    = $app->getClientLang();
$langItems     = array();
foreach($config->langs as $key => $value) $langItems[] = array('text' => $value, 'data-on' => 'click', 'data-call' => 'switchLang', 'data-params' => $key, 'active' => $key == $clientLang);

$pluginTips      = '';
$expiredPlugins  = implode('、', $plugins['expired']);
$expiringPlugins = implode('、', $plugins['expiring']);
$expiredTips     = sprintf($lang->misc->expiredPluginTips, $expiredPlugins);
$expiringTips    = sprintf($lang->misc->expiringPluginTips, $expiringPlugins);
if($expiredPlugins)  $pluginTips = $expiredTips;
if($expiringPlugins) $pluginTips = $expiringTips;
if($expiredPlugins and $expiringPlugins) $pluginTips = $expiredTips . $pluginTips;
$pluginTotal = count($plugins['expired']) + count($plugins['expiring']);
$expiredCountTips = sprintf($lang->misc->expiredCountTips, $pluginTips, $pluginTotal);

$demoUserItems = array();
if(!empty($this->config->global->showDemoUsers))
{
    $demoPassword = '123456';
    $md5Password  = md5('123456');
    $demoUsers    = 'productManager,projectManager,dev1,dev2,dev3,tester1,tester2,tester3,testManager';
    $demoUsers    = $this->dao->select('account,password,realname')->from(TABLE_USER)->where('account')->in($demoUsers)->andWhere('deleted')->eq(0)->andWhere('password')->eq($md5Password)->fetchAll('account');

    $link  = inlink('login');
    $link .= strpos($link, '?') !== false ? '&' : '?';
    foreach($demoUsers as $demoAccount => $demoUser)
    {
        if($demoUser->password != $md5Password) continue;
        $demoUserItems[] = a(set::href('#'), set::onclick('window.demoSubmit(this)'), set('data-account', $demoAccount), set('data-password', md5($md5Password . $this->session->rand)), $demoUser->realname);
    }
}

if($unsafeSites and !empty($unsafeSites[$zentaoDirName]))
{
    $paths     = array();
    $databases = array();
    $isXampp   = false;
    foreach($unsafeSites as $webRoot => $site)
    {
        $path = $site['path'];
        if(strpos($path, 'xampp') !== false) $isXampp = true;

        $paths[]     = $site['path'];
        $databases[] = $site['database'];
    }

    $process4Safe = $isXampp ? $lang->user->process4DB : $lang->user->process4DIR;
    $process4Safe = sprintf($process4Safe, join(' ', $isXampp ? $databases : $paths));
    jsVar('process4Safe', $process4Safe);
}
jsVar('loginTimeoutTip', $lang->user->error->loginTimeoutTip);

$copy         = isset($lang->user->loginPage) ? $lang->user->loginPage : null;
$brand        = $copy?->brand        ?? '健忻项目管理系统';
$brandEn      = $copy?->brandEn      ?? 'Jianxin Project Management System';
$headlineLead = $copy?->headlineLead ?? '智能协同，';
$headlineTail = $copy?->headlineTail ?? '高效推进';
$subhead      = $copy?->subhead      ?? '驱动项目全流程管理';
$desc         = $copy?->desc         ?? '覆盖项目规划、进度跟踪、任务协同、资源调度与数据分析，实现项目全流程透明管理，助力团队高效决策与执行。';
$welcome      = $copy?->welcome      ?? '欢迎登录';
$welcomeHint  = $copy?->welcomeHint  ?? '登录 健忻项目管理系统';
$accountPh    = $copy?->accountPh    ?? '请输入用户名';
$passwordPh   = $copy?->passwordPh   ?? '请输入密码';
$remember     = $copy?->remember     ?? '记住账号';
$trust        = $copy?->trust        ?? '值得信赖的医疗科技项目管理平台';
$copyright    = $copy?->copyright    ?? '© 2026 健忻项目管理系统. All rights reserved.';
$features     = (isset($copy->features) && $copy->features) ? $copy->features : array(
    'plan'    => array('title' => '项目规划', 'desc' => '科学计划，高效启动'),
    'track'   => array('title' => '进度跟踪', 'desc' => '实时监控，精准掌控'),
    'collab'  => array('title' => '任务协同', 'desc' => '高效协作，快速响应'),
    'insight' => array('title' => '数据洞察', 'desc' => '数据驱动，智能决策')
);
$featureNodes = array();
foreach($features as $key => $item)
{
    $featureTitle = is_array($item) ? ($item['title'] ?? '') : ($item->title ?? '');
    $featureDesc  = is_array($item) ? ($item['desc'] ?? '') : ($item->desc ?? '');
    $featureNodes[] = div
    (
        setClass("jx-login-feature is-{$key}"),
        div(setClass('jx-login-feature-icon')),
        div(setClass('jx-login-feature-title'), $featureTitle),
        div(setClass('jx-login-feature-desc'), $featureDesc)
    );
}

$imgRoot  = $config->webRoot . 'theme/default/images/main/';
$logoSrc  = $imgRoot . 'jx-logo.png';
$bgFile   = $this->app->getWwwRoot() . 'theme' . DS . 'default' . DS . 'images' . DS . 'main' . DS . 'jx-login-bg.jpg';
$bgSrc    = $imgRoot . 'jx-login-bg.jpg?v=' . (is_file($bgFile) ? filemtime($bgFile) : time());
$iconShield = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M12 3.5l7 2.6v6.2c0 4.3-2.8 7.4-7 8.7-4.2-1.3-7-4.4-7-8.7V6.1l7-2.6z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.8 12.1l2.2 2.2 4.3-4.4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>';

set::zui(true);
set::bodyClass('jx-login-page');
set::className('jx-login-html');

div
(
    setID('main'),
    setClass('no-padding jx-login-shell'),
    div(setClass('jx-login-bg'), set::style(array('background-image' => 'url(' . $bgSrc . ')'))),
    div
    (
        setID('login'),
        setClass('jx-login'),
        div
        (
            setClass('jx-login-brand'),
            h::img(setClass('jx-login-logo'), set::src($logoSrc), set::alt($brand)),
            div
            (
                setClass('jx-login-brand-text'),
                div(setClass('jx-login-brand-zh'), $brand),
                div(setClass('jx-login-brand-en'), $brandEn)
            )
        ),
        div
        (
            setID('loginPanel'),
            setClass('jx-login-stage'),
            div
            (
                setClass('jx-login-hero'),
                h1
                (
                    setClass('jx-login-headline'),
                    span(setClass('jx-login-headline-lead'), $headlineLead),
                    span(setClass('jx-login-headline-tail'), $headlineTail)
                ),
                h2(setClass('jx-login-subhead'), $subhead),
                p(setClass('jx-login-desc'), $desc),
                div(setClass('jx-login-features'), $featureNodes)
            ),
            div
            (
                setID('loginBox'),
                setClass('jx-login-card'),
                div
                (
                    setClass('header jx-login-card-head'),
                    div
                    (
                        setClass('jx-login-card-titles'),
                        h2(setClass('font-bold'), $welcome),
                        p(setClass('jx-login-card-hint'), $welcomeHint)
                    ),
                    dropdown
                    (
                        setID('langs'),
                        setClass('actions btn jx-login-lang'),
                        set::title('Change Language/更换语言/更換語言'),
                        set::items($langItems),
                        set::menuClass('langsDropMenu'),
                        set::staticMenu(true),
                        set::trigger('hover'),
                        html($config->langs[$clientLang])
                    )
                ),
                $loginExpired ? p(setClass('text-danger loginExpired'), $lang->user->loginExpired) : null,
                form
                (
                    set::layout('grid'),
                    on::click('#submit', 'safeSubmit'),
                    set::requiredFields(false),
                    setID('loginForm'),
                    setClass('jx-login-form'),
                    formGroup
                    (
                        set::width('full'),
                        set::label(false),
                        div
                        (
                            setClass('jx-login-field is-user'),
                            input(set(array('name' => 'account', 'id' => 'account', 'placeholder' => $accountPh, 'autocomplete' => 'username')))
                        )
                    ),
                    formGroup
                    (
                        set::width('full'),
                        set::label(false),
                        div
                        (
                            setClass('jx-login-field is-lock'),
                            input(set(array('type' => 'password', 'name' => 'password', 'id' => 'password', 'placeholder' => $passwordPh, 'autocomplete' => 'current-password'))),
                            span(setID('togglePassword'), setClass('jx-pwd-toggle'), set('role', 'button'), set('tabindex', '0'), set('aria-label', $lang->user->password))
                        )
                    ),
                    !empty($this->config->safe->loginCaptcha) ? formGroup
                    (
                        set::width('full'),
                        set::label(false),
                        div
                        (
                            setClass('captchaBox'),
                            inputGroup
                            (
                                input(set::name('captcha'), set::placeholder($lang->user->captcha)),
                                span(setClass('input-group-addon'), h::img(set::src($this->createLink('misc', 'captcha')), on::click('refreshCaptcha(e.target)'), set::style(array('height' => '2.1rem'))))
                            )
                        )
                    ) : null,
                    formGroup
                    (
                        setID('loginOptions'),
                        set::width('full'),
                        set::control(array('control' => 'checkList', 'items' => array('on' => $remember), 'name' => 'keepLogin', 'value' => $keepLogin)),
                        a
                        (
                            set('href', $resetLink),
                            set('class', 'resetPassword'),
                            $lang->user->forgetPassword
                        )
                    ),
                    formHidden('referer', $referer),
                    set::actions(array
                    (
                        array('text' => $lang->login, 'id' => 'submit', 'class' => 'primary jx-login-submit', 'btnType' => 'submit'),
                        $app->company->guest ? array('text' => $lang->user->asGuest, 'class' => 'w-full not-open-url', 'url' => createLink($config->default->module)) : null
                    ))
                )
            ),
            (count($plugins['expired']) > 0 || count($plugins['expiring']) > 0) ? div
            (
                setClass('table-row-extension'),
                div
                (
                    setID('notice'),
                    setClass('alert secondary'),
                    div(setClass('content'), icon(setClass('text-secondary'), 'exclamation-sign'), html($expiredCountTips))
                )
            ) : null,
            empty($demoUsers) ? null : div
            (
                setClass('footer jx-login-demo'),
                span($lang->user->loginWithDemoUser),
                $demoUserItems
            )
        ),
        div
        (
            setID('info'),
            div
            (
                setID('poweredby'),
                ($unsafeSites && !empty($unsafeSites[$zentaoDirName])) ? div(a(setClass('showNotice'), set::href('###'), on::click('showNotice'), $lang->user->notice4Safe)) : null,
                $config->checkVersion ? h::iframe(setID('updater'), setClass('hidden'), set::src(createLink('misc', 'checkUpdate', "sn=$sn"))) : null
            )
        )
    ),
    div
    (
        setClass('jx-login-footer'),
        div
        (
            setClass('jx-login-footer-trust'),
            html($iconShield),
            span($trust)
        ),
        div(setClass('jx-login-footer-copy'), $copyright)
    )
);

render('pagebase');
