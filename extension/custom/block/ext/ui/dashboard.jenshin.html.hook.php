<?php
namespace zin;

$blocks    = data('blocks');
$dashboard = data('dashboard');
if(!empty($blocks) || empty($dashboard)) return;

query('#mainContent')->append
(
    div
    (
        setClass('jx-dash-empty'),
        div(setClass('jx-dash-empty-title'), '项目看板还没有区块'),
        div(setClass('jx-dash-empty-desc'), '当前仪表盘是空的。恢复默认布局后会显示本项目的计划、总览和最新动态。'),
        a
        (
            setClass('btn primary'),
            set::href(createLink('block', 'reset', "dashboard={$dashboard}")),
            set('data-confirm', '是否恢复默认布局？'),
            '恢复默认布局'
        )
    )
);
