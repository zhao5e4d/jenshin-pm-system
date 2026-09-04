/**
 * 将任务记录标题拆成「操作说明 + 时间」，更接近列表行。
 * HistoryPanel 由 ZUI 异步渲染，需在面板出现后持续处理。
 */
function jxEnhanceTaskRecords()
{
    const root = document.querySelector('.m-task-view, .m-task-edit') || document;
    root.querySelectorAll('.history-panel-action').forEach(function(item)
    {
        if(item.dataset.jxEnhanced === '1') return;

        const titleEl = item.querySelector('.item-title');
        if(!titleEl) return;

        const source = titleEl.querySelector('div') || titleEl;
        const html = (source.innerHTML || '').trim();
        const match = html.match(/^(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}),\s*([\s\S]+)$/);
        if(!match) return;

        item.dataset.jxEnhanced = '1';
        titleEl.classList.add('jx-task-record-title');
        titleEl.innerHTML = '<span class="jx-task-record-desc">' + match[2] + '</span><span class="jx-task-record-time">' + match[1] + '</span>';
    });
}

(function()
{
    if(!document.querySelector('.m-task-view, .m-task-edit')) return;

    let observed = false;
    const watchPanel = function()
    {
        const panel = document.querySelector('.history-panel');
        if(!panel || observed) return;
        observed = true;
        new MutationObserver(jxEnhanceTaskRecords).observe(panel, {childList: true, subtree: true});
    };

    const run = function()
    {
        watchPanel();
        jxEnhanceTaskRecords();
    };

    const start = function()
    {
        run();
        let n = 0;
        const timer = setInterval(function()
        {
            run();
            if(++n >= 25) clearInterval(timer);
        }, 200);
    };

    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
    document.addEventListener('pagerender.app', run);
})();
