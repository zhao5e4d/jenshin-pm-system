window.jxScreenQuery = function()
{
    const root = document.querySelector('.jx-screen-filters');
    const read = function(name, fallback)
    {
        if(!root) return fallback;
        const node = root.querySelector('[name=' + name + ']');
        return node ? (node.value || fallback) : fallback;
    };

    const period = read('period', 'week') || 'week';
    let begin = '';
    let end = '';
    if(period === 'custom')
    {
        begin = String(read('begin', '')).replace(/-/g, '');
        end = String(read('end', '')).replace(/-/g, '');
    }

    return 'dept=' + encodeURIComponent(read('dept', ''))
        + '&product=' + encodeURIComponent(read('product', '0') || '0')
        + '&status='
        + '&health=' + encodeURIComponent(read('health', ''))
        + '&period=' + encodeURIComponent(period)
        + '&begin=' + encodeURIComponent(begin)
        + '&end=' + encodeURIComponent(end)
        + '&focus=';
};

window.jxScreenChange = function()
{
    const url = $.createLink('jxboard', 'screen', window.jxScreenQuery());
    if(typeof loadPage === 'function') loadPage(url);
    else window.location.href = url;
};

window.jxScreenToggleFull = function()
{
    const root = document.getElementById('jxMeetingScreen');
    if(!root) return;
    if(document.fullscreenElement) document.exitFullscreen();
    else if(root.requestFullscreen) root.requestFullscreen();
};

window.jxScreenTick = function()
{
    const clock = document.getElementById('jxScreenClock');
    if(!clock) return;
    const now = new Date();
    const pad = function(n){ return n < 10 ? '0' + n : String(n); };
    const date = now.getFullYear() + '.' + pad(now.getMonth() + 1) + '.' + pad(now.getDate());
    const time = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    const dateNode = clock.querySelector('.jx-screen-clock-date');
    const timeNode = clock.querySelector('.jx-screen-clock-time');
    if(dateNode && timeNode)
    {
        dateNode.textContent = date;
        timeNode.textContent = time;
        return;
    }
    clock.textContent = date + ' ' + time;
};

window.jxScreenCountUp = function()
{
    const nodes = document.querySelectorAll('.jx-screen-kpi-value[data-count]');
    nodes.forEach(function(node)
    {
        const target = parseFloat(node.getAttribute('data-count') || '0');
        if(!isFinite(target)) return;
        const start = performance.now();
        const duration = 900;
        const tick = function(now)
        {
            const p = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - p, 3);
            node.textContent = String(Math.round(target * eased));
            if(p < 1) requestAnimationFrame(tick);
            else node.textContent = String(Math.round(target));
        };
        requestAnimationFrame(tick);
    });
};

window.jxScreenResizeCharts = function()
{
    if(!window.echarts || typeof window.echarts.getInstanceByDom !== 'function') return;
    document.querySelectorAll('.jx-screen .echarts-container, .jx-screen [id^="zin_echart_"]').forEach(function(node)
    {
        const chart = window.echarts.getInstanceByDom(node);
        if(chart) chart.resize();
    });
};

$(function()
{
    window.jxScreenTick();
    if(window.jxScreenClockTimer) clearInterval(window.jxScreenClockTimer);
    window.jxScreenClockTimer = setInterval(window.jxScreenTick, 1000);
    window.jxScreenCountUp();

    $(document).off('change.jxScreen').on('change.jxScreen', '.jx-screen-filters [name=dept], .jx-screen-filters [name=product], .jx-screen-filters [name=health], .jx-screen-filters [name=period], .jx-screen-filters [name=begin], .jx-screen-filters [name=end]', window.jxScreenChange);
    $(document).off('click.jxScreenFull').on('click.jxScreenFull', '.jx-screen-full', function(event)
    {
        event.preventDefault();
        window.jxScreenToggleFull();
    });
    $(document).off('fullscreenchange.jxScreen').on('fullscreenchange.jxScreen', function()
    {
        const btn = document.querySelector('.jx-screen-full');
        const lang = (window.lang && window.lang.jxboard) ? window.lang.jxboard : {};
        if(btn) btn.textContent = document.fullscreenElement ? (lang.exitFullscreen || '退出全屏') : (lang.fullscreen || '全屏投影');
        setTimeout(window.jxScreenResizeCharts, 160);
    });
});
