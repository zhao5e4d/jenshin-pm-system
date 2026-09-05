/**
 * 点其它一级菜单时，关掉工作台 iframe 里还开着的欢迎弹窗。
 */
function jxHideWorkbenchGuide()
{
    try
    {
        const iframe = document.getElementById('appIframe-my');
        if(!iframe || !iframe.contentWindow) return;
        const win = iframe.contentWindow;
        if(typeof win.jxHideGuideModal === 'function')
        {
            win.jxHideGuideModal();
            return;
        }
        if(win.zui && win.zui.Modal)
        {
            const inst = win.zui.Modal.query('#featureNoticeModal');
            if(inst && typeof inst.hide === 'function') inst.hide();
        }
        if(win.$) win.$('#featureNoticeModal').removeClass('show in').hide();
    }
    catch (err) {}
}

$(function()
{
    $(document).on('click.jxguide', '#menuMainNav a[data-app], #menuMoreNav a[data-app]', function()
    {
        const app = $(this).attr('data-app') || $(this).data('app');
        if(app && String(app) !== 'my') jxHideWorkbenchGuide();
    });
});
