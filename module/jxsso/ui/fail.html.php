<?php
declare(strict_types=1);
namespace zin;

$visual = html('<svg class="jx-sso-fail-svg" viewBox="0 0 96 96" fill="none" aria-hidden="true"><rect x="18" y="22" width="60" height="52" rx="16" fill="currentColor" opacity=".16"/><path d="M36 42.5V36c0-6.627 5.373-12 12-12s12 5.373 12 12v6.5" stroke="currentColor" stroke-width="4" stroke-linecap="round"/><rect x="30" y="42" width="36" height="28" rx="10" fill="currentColor"/><circle cx="48" cy="54" r="4.5" fill="#fff"/><path d="M48 58.5v6" stroke="#fff" stroke-width="3.2" stroke-linecap="round"/></svg>');

$step = function(string $index, string $title, string $hint)
{
    return div
    (
        setClass('jx-sso-fail-step'),
        div(setClass('jx-sso-fail-step-index'), $index),
        div
        (
            setClass('jx-sso-fail-step-body'),
            div(setClass('jx-sso-fail-step-title'), $title),
            div(setClass('jx-sso-fail-step-hint'), $hint)
        )
    );
};

div
(
    setClass('jx-sso-fail'),
    div(setClass('jx-sso-fail-glow is-a')),
    div(setClass('jx-sso-fail-glow is-b')),
    div
    (
        setClass('jx-sso-fail-card'),
        div(setClass('jx-sso-fail-visual'), $visual),
        div(setClass('jx-sso-fail-badge'), $lang->jxsso->failBadge),
        h2(setClass('jx-sso-fail-title'), $lang->jxsso->failTitle),
        p(setClass('jx-sso-fail-msg'), $message),
        div
        (
            setClass('jx-sso-fail-help'),
            div(setClass('jx-sso-fail-help-label'), $lang->jxsso->failHelp),
            div
            (
                setClass('jx-sso-fail-steps'),
                $step('1', $lang->jxsso->failStep1, $lang->jxsso->failStep1Hint),
                $step('2', $lang->jxsso->failStep2, $lang->jxsso->failStep2Hint),
                $step('3', $lang->jxsso->failStep3, $lang->jxsso->failStep3Hint)
            )
        ),
        div
        (
            setClass('jx-sso-fail-actions'),
            btn
            (
                setClass('primary jx-sso-fail-back'),
                set::icon('back'),
                set::onclick('history.back()'),
                $lang->jxsso->backPrev
            )
        ),
        div(setClass('jx-sso-fail-foot'), $lang->jxsso->failFoot)
    )
);
