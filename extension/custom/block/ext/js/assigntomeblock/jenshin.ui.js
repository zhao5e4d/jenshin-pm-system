/** 协同事项标题保留原链接，只追加未读提示。 */
window.renderJxCollabCell = function(result, info)
{
    result = window.renderCell(result, info);
    if(info.col.name === 'title' && info.row.data.isUnread)
    {
        result.push({html: '<span class="jx-collab-unread" role="img" title="' + jxCollabNewItemLabel + '" aria-label="' + jxCollabNewItemLabel + '"></span>'});
    }
    return result;
};

/** 更新行数据，避免虚拟列表滚动或切换页签时重新出现红点。 */
window.clearJxCollabUnread = function(collabID)
{
    $('.assigntome-jxcollab [zui-create-dtable]').each(function()
    {
        const table = $(this).zui('dtable');
        if(!table || !Array.isArray(table.options.data)) return;
        const data = table.options.data.map(item => String(item.id) === String(collabID) ? {...item, isUnread: false} : item);
        table.render({data});
    });
};
