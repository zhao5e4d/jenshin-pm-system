;(function()
{
    const hideModelSwitcher = function()
    {
        document.querySelectorAll('.panel-title .dropdown, .panel-heading .dropdown, .panel-title .gray-300-outline.rounded-full').forEach(function(el)
        {
            el.remove();
        });
    };

    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', hideModelSwitcher);
    else hideModelSwitcher();
    document.addEventListener('pagerender.app', hideModelSwitcher);
})();
