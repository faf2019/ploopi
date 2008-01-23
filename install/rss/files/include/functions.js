function rss_explorer_catlist_get()
{
	ploopi_ajaxloader('rss_explorer_catlist');
	ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=rss_explorer_catlist_get', '', 'rss_explorer_catlist');

	rss_explorer_feed_get();
}

function rss_explorer_catlist_choose(rsscat_id)
{
	ploopi_ajaxloader('rss_explorer_catlist');
	ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=rss_explorer_catlist_get&rsscat_id='+rsscat_id, '', 'rss_explorer_catlist');

	rss_explorer_feedlist_get(rsscat_id);
	rss_explorer_feed_get(rsscat_id);
}

function rss_explorer_feedlist_get(rsscat_id)
{
	ploopi_ajaxloader('rss_explorer_feedlist');
	ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=rss_explorer_feedlist_get&rsscat_id='+rsscat_id, '', 'rss_explorer_feedlist');
}

function rss_explorer_feedlist_choose(rsscat_id, rssfeed_id)
{
	ploopi_ajaxloader('rss_explorer_feedlist');
	ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=rss_explorer_feedlist_get&rsscat_id='+rsscat_id+'&rssfeed_id='+rssfeed_id, '', 'rss_explorer_feedlist');

	rss_explorer_feed_get(rsscat_id, rssfeed_id);
}

function rss_explorer_feed_get(rsscat_id, rssfeed_id)
{
	if (isNaN(rsscat_id)) rsscat_id = '';
	if (isNaN(rssfeed_id)) rssfeed_id = ''

	ploopi_ajaxloader('rss_explorer_feed');
	ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=rss_explorer_feed_get&rsscat_id='+rsscat_id+'&rssfeed_id='+rssfeed_id, '', 'rss_explorer_feed');
}
