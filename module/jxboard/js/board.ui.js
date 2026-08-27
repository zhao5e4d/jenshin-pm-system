window.jxBoardChange = function()
{
    const root = document.querySelector('.pm-dash-filters');
    if(!root) return;
    const dept    = (root.querySelector('[name=dept]') || {}).value || '';
    const product = (root.querySelector('[name=product]') || {}).value || '0';
    const status  = (root.querySelector('[name=status]') || {}).value || '';
    const method  = (window.config && window.config.currentMethod) ? window.config.currentMethod : 'overview';
    const url = $.createLink('jxboard', method, 'dept=' + encodeURIComponent(dept) + '&product=' + encodeURIComponent(product) + '&status=' + encodeURIComponent(status));
    if(typeof loadPage === 'function') loadPage(url);
    else window.location.href = url;
};

$(function()
{
    $(document).off('change.jxBoard').on('change.jxBoard', '.pm-dash-filters [name=dept], .pm-dash-filters [name=product], .pm-dash-filters [name=status]', window.jxBoardChange);
});
