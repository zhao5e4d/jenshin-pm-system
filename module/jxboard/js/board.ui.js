window.jxBoardQuery = function(extra)
{
    const root = document.querySelector('.pm-dash-filters');
    extra = extra || {};
    const read = function(name, fallback)
    {
        if(Object.prototype.hasOwnProperty.call(extra, name)) return extra[name];
        if(!root) return fallback;
        const node = root.querySelector('[name=' + name + ']');
        return node ? (node.value || fallback) : fallback;
    };

    const period = read('period', 'month') || 'month';
    let begin = '';
    let end = '';
    if(period === 'custom')
    {
        begin = String(read('begin', '')).replace(/-/g, '');
        end   = String(read('end', '')).replace(/-/g, '');
    }

    return 'dept=' + encodeURIComponent(read('dept', ''))
        + '&product=' + encodeURIComponent(read('product', '0') || '0')
        + '&status=' + encodeURIComponent(read('status', ''))
        + '&health=' + encodeURIComponent(read('health', ''))
        + '&period=' + encodeURIComponent(period)
        + '&begin=' + encodeURIComponent(begin)
        + '&end=' + encodeURIComponent(end)
        + '&focus=';
};

window.jxBoardChange = function()
{
    const method = (window.config && window.config.currentMethod) ? window.config.currentMethod : 'overview';
    const url = $.createLink('jxboard', method, window.jxBoardQuery());
    if(typeof loadPage === 'function') loadPage(url);
    else window.location.href = url;
};

window.jxBoardPrint = function(event)
{
    if(event) event.preventDefault();
    window.print();
    return false;
};

$(function()
{
    $(document).off('change.jxBoard').on('change.jxBoard', '.pm-dash-filters [name=dept], .pm-dash-filters [name=product], .pm-dash-filters [name=status], .pm-dash-filters [name=health], .pm-dash-filters [name=period], .pm-dash-filters [name=begin], .pm-dash-filters [name=end]', window.jxBoardChange);
    $(document).off('click.jxBoardPrint').on('click.jxBoardPrint', '.pm-print-board', window.jxBoardPrint);
    $(document).off('click.jxBoardExport').on('click.jxBoardExport', '.pm-export-csv', function(event)
    {
        event.preventDefault();
        event.stopPropagation();
        const href = this.getAttribute('href');
        if(href) window.location.href = href;
        return false;
    });

    const focus = document.querySelector('.pm-dash-panel.is-focus');
    if(focus && typeof focus.scrollIntoView === 'function')
    {
        setTimeout(function(){ focus.scrollIntoView({behavior: 'smooth', block: 'start'}); }, 80);
    }
});
