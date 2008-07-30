function rss_explorer_catlist_get()
{
    ploopi_ajaxloader('rss_explorer_catlist');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_explorer_catlist_get', 'rss_explorer_catlist');

    rss_explorer_feed_get();
}

function rss_explorer_catlist_choose(rsscat_id)
{
    ploopi_innerHTML('rss_explorer_catlist', ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_explorer_catlist_get&rsscat_id='+rsscat_id));

    rss_explorer_feedlist_get();
    rss_explorer_feed_get();
}

function rss_explorer_feedlist_get()
{
    ploopi_ajaxloader('rss_explorer_feedlist');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_explorer_feedlist_get', '', 'rss_explorer_feedlist');
}

function rss_explorer_feedlist_choose(rssfeed_id)
{
    ploopi_innerHTML('rss_explorer_feedlist', ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_explorer_feedlist_get&rssfeed_id='+rssfeed_id));

    rss_explorer_feed_get();
}

function rss_explorer_feed_get(rss_search_kw)
{
    if (rss_search_kw == undefined) rss_search_kw = '%%undefined%%';

    ploopi_ajaxloader('rss_explorer_feed');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_explorer_feed_get&rss_search_kw='+rss_search_kw, 'rss_explorer_feed');
}
