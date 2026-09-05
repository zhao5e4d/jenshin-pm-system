/**
 * 点「立即体验」先写已读，成功后再关弹窗。
 * 关叉、只翻页、刷新：下次登录仍弹。
 */
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
    $(document).on('click', '.jx-dismiss-guide', jxDismissGuide);
    setTimeout(jxBindGuideDismiss, 200);
    setTimeout(jxBindGuideDismiss, 800);
});
