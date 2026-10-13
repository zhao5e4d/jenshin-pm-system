/** 详情已插入页面并可见后，才保存当前用户的阅读状态。 */
window.markJxCollabRead = function(element, collabID, url)
{
    const detail = $(element);
    const deadline = Date.now() + 10000;
    const afterVisible = function()
    {
        if(!detail.length || !detail[0].isConnected) return;
        if(!detail[0].getClientRects().length || window.getComputedStyle(detail[0]).visibility === 'hidden')
        {
            if(Date.now() < deadline) window.setTimeout(afterVisible, 50);
            return;
        }
        $.ajax({url, type: 'POST', dataType: 'json', data: {action: 'markRead'}}).done(function(response)
        {
            if(!response || response.result !== 'success' || Number(response.collabID) !== Number(collabID)) return;
            detail.attr('data-jx-collab-unread', '0');
            if(typeof window.clearJxCollabUnread === 'function') window.clearJxCollabUnread(collabID);
        });
    };
    window.requestAnimationFrame(afterVisible);
};

$(function()
{
    const detail = $('#jxCollabDetail[data-jx-collab-unread="1"]').last();
    if(detail.length) window.markJxCollabRead(detail, Number(detail.attr('data-jx-collab-id')), detail.attr('data-jx-read-url'));
});
