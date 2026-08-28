;(function()
{
    const strip = function()
    {
        document.querySelectorAll('[href*="createGuide"], [url*="createGuide"], [data-url*="createGuide"], .create-project-btn').forEach(function(el)
        {
            el.removeAttribute('data-toggle');
            el.removeAttribute('data-type');
            el.removeAttribute('data-position');
        });
    };
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', strip);
    else strip();
    document.addEventListener('pagerender.app', strip);
})();
