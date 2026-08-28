window.renderRowData = function($row, index, row)
{
    const product = row;
    const applyLine = function($line)
    {
        if(!$line) return;

        const pairs = (typeof lines === 'object' && lines)
            ? (lines[product.program] || lines[0] || lines[''] || {})
            : {};
        const lineItems = [];
        $.each(pairs, function(lineID, lineName)
        {
            lineItems.push({value: String(lineID), text: lineName});
        });
        if(lineItems.length && $line.render) $line.render({items: lineItems});

        const currentValue = product.line ? String(product.line) : '';
        if(currentValue && $line.$) $line.$.setValue(currentValue);
    };

    $row.find('[data-name="line"]').find('.picker-box').on('inited', function(e, info)
    {
        applyLine(info[0]);
    });
}

window.loadProductLines = function()
{
    const $currentRow = $(event.target).closest('tr');
    const programID   = $currentRow.find('input[name^=program]').val() || 0;
    const lineID      = $currentRow.find('input[name^=line]').val();
    const pairs       = (typeof lines === 'object' && lines && lines[programID]) ? lines[programID] : ((typeof lines === 'object' && lines && lines[0]) ? lines[0] : {});
    const lineItems   = [];
    $.each(pairs, function(id, name) { lineItems.push({value: String(id), text: name}); });
    const picker = $currentRow.find('input[name^="line"]').zui('picker');
    if(!picker) return;
    picker.render({items: lineItems});
    if(lineID) picker.$.setValue(String(lineID));
}
