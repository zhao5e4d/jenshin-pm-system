;(function()
{
    const strip = function()
    {
        document.querySelectorAll('[href*="createGuide"], [url*="createGuide"], [data-url*="createGuide"], .create-project-btn').forEach(function(el)
        {
            ['href', 'url', 'data-url'].forEach(function(attr)
            {
                const value = el.getAttribute(attr);
                if(value) el.setAttribute(attr, value.replace(/project-createGuide(?:-(\d+))?\.html/, function(_, id){ return id ? ('project-create-scrum-' + id + '.html') : 'project-create-scrum.html'; }));
            });
            el.removeAttribute('data-toggle');
            el.removeAttribute('data-type');
            el.removeAttribute('data-position');
        });
    };
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', strip);
    else strip();
    document.addEventListener('pagerender.app', strip);
})();
