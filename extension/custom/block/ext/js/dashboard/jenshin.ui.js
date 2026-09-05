/**
 * 点「立即体验」先写已读，成功后再关弹窗。
 * 关叉、只翻页、刷新：下次登录仍弹。
 * 点其它一级菜单：关掉工作台弹窗，避免盖在新产品组合/项目等页面上。
 * 回到工作台且未点「立即体验」时再打开。
 */
let jxGuideDismissed = false;

function jxBindGuideDismiss()
{
    $('#featureNoticeModal .page-block').last().find('.primary, .btn-primary').each(function()
    {
        $(this).addClass('jx-dismiss-guide').removeAttr('data-dismiss').removeAttr('data-bs-dismiss');
    });
}

function jxHideGuideModal()
{
    const inst = (typeof zui !== 'undefined' && zui.Modal) ? zui.Modal.query('#featureNoticeModal') : null;
    if(inst && typeof inst.hide === 'function') inst.hide();
    else $('#featureNoticeModal').removeClass('show in').hide();
}
window.jxHideGuideModal = jxHideGuideModal;

function jxShowGuideIfWorkbench()
{
    if(jxGuideDismissed || !$('#featureNoticeModal').length) return;
    const code = jxParentAppCode();
    if(code && code !== 'my') return;
    if(typeof zui !== 'undefined' && zui.Modal) zui.Modal.open({id: 'featureNoticeModal'});
}
window.jxShowGuideIfWorkbench = jxShowGuideIfWorkbench;

function jxParentAppCode()
{
    try
    {
        const parentWin = window.parent;
        if(!parentWin || parentWin === window || !parentWin.$) return '';
        return String(parentWin.$('body').attr('data-app') || '');
    }
    catch (err)
    {
        return '';
    }
}

function jxBindGuideLeaveWorkbench()
{
    try
    {
        const parentWin = window.parent;
        if(!parentWin || parentWin === window || !parentWin.$) return;

        const $nav = parentWin.$('#menuMainNav, #menuMoreNav, #appTabs');
        $nav.off('click.jxguide').on('click.jxguide', 'a[data-app]', function()
        {
            const app = parentWin.$(this).attr('data-app') || parentWin.$(this).data('app');
            if(app && String(app) !== 'my') jxHideGuideModal();
        });

        parentWin.$('#apps').off('openapp.apps.jxguide').on('openapp.apps.jxguide', function()
        {
            const code = jxParentAppCode();
            if(code && code !== 'my') jxHideGuideModal();
            else if(code === 'my') jxShowGuideIfWorkbench();
        });
    }
    catch (err) {}
}

function jxUnbindGuideLeaveWorkbench()
{
    try
    {
        const parentWin = window.parent;
        if(!parentWin || parentWin === window || !parentWin.$) return;
        parentWin.$('#menuMainNav, #menuMoreNav, #appTabs').off('click.jxguide');
        parentWin.$('#apps').off('openapp.apps.jxguide');
    }
    catch (err) {}
}

function jxDismissGuide(e)
{
    if(e)
    {
        e.preventDefault();
        e.stopImmediatePropagation();
        e.stopPropagation();
    }

    const $btn = $(e && e.currentTarget ? e.currentTarget : '.jx-dismiss-guide');
    if($btn.data('jxSaving')) return false;
    $btn.data('jxSaving', 1).addClass('disabled');

    $.post($.createLink('misc', 'ajaxDismissFeatureNotice'), function(res)
    {
        let ok = true;
        if(typeof res === 'string')
        {
            try { res = JSON.parse(res); } catch (err) { res = {}; }
        }
        if(res && res.result && res.result !== 'success') ok = false;
        if(ok)
        {
            jxGuideDismissed = true;
            jxHideGuideModal();
            return;
        }
        $btn.data('jxSaving', 0).removeClass('disabled');
    }).fail(function()
    {
        $btn.data('jxSaving', 0).removeClass('disabled');
    });
    return false;
}

$(function()
{
    if(!$('#featureNoticeModal').length) return;

    jxBindGuideDismiss();
    jxBindGuideLeaveWorkbench();
    $(document).on('click', '.jx-dismiss-guide', jxDismissGuide);
    $(document).on('pageunmount.app', function()
    {
        jxHideGuideModal();
        jxUnbindGuideLeaveWorkbench();
    });
    setTimeout(jxBindGuideDismiss, 200);
    setTimeout(jxBindGuideDismiss, 800);
});
