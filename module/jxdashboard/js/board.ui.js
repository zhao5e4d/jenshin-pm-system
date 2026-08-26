window.jxDashChange = function()
{
    const root = document.querySelector('.jx-dash-filters');
    if(!root) return;
    const biz  = (root.querySelector('[name=bizType]') || {}).value || '';
    const dept = (root.querySelector('[name=dept]') || {}).value || '';
    const method = (window.config && window.config.currentMethod) ? window.config.currentMethod : 'overview';
    const url = $.createLink('jxdashboard', method, 'bizType=' + encodeURIComponent(biz) + '&dept=' + encodeURIComponent(dept));
    if(typeof loadPage === 'function') loadPage(url);
    else window.location.href = url;
};

$(function()
{
    $(document).off('change.jxDash').on('change.jxDash', '.jx-dash-filters [name=bizType], .jx-dash-filters [name=dept]', window.jxDashChange);
});
