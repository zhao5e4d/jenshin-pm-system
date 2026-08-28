;(function()
{
    const toScrumCreate = function(url)
    {
        if(!url || url.indexOf('createGuide') === -1) return url;
        return url.replace(/project-createGuide(?:-(\d+))?\.html/, function(_, programID)
        {
            return programID ? ('project-create-scrum-' + programID + '.html') : 'project-create-scrum.html';
        });
    };

    const stripCreateGuideModal = function()
    {
        const nodes = document.querySelectorAll('[href*="createGuide"], [url*="createGuide"], [data-url*="createGuide"], .create-project-btn');
        nodes.forEach(function(el)
        {
            ['href', 'url', 'data-url'].forEach(function(attr)
            {
                const value = el.getAttribute(attr);
                if(value && value.indexOf('createGuide') !== -1) el.setAttribute(attr, toScrumCreate(value));
            });
            el.removeAttribute('data-toggle');
            el.removeAttribute('data-type');
            el.removeAttribute('data-position');
        });
    };

    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', stripCreateGuideModal);
    else stripCreateGuideModal();
    document.addEventListener('pagerender.app', stripCreateGuideModal);
})();
