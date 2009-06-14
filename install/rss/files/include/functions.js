/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2008 HeXad

    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

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
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_explorer_feedlist_get', 'rss_explorer_feedlist');
}

function rss_explorer_feedlist_choose(rssfeed_id)
{
    ploopi_innerHTML('rss_explorer_feedlist', ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_explorer_feedlist_get&rssfeed_id='+rssfeed_id));

    rss_explorer_feed_get();
}

function rss_explorer_feed_get(rss_search_kw)
{
    if (typeof(rss_search_kw) == 'undefined') rss_search_kw = '';

    ploopi_ajaxloader('rss_explorer_feed');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_explorer_feed_get&rss_search_kw='+rss_search_kw, 'rss_explorer_feed');
}

// REQUEST
function rss_filter_feed_get()
{
   ploopi_ajaxloader('rss_filter_feed');
   ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_filter_feed_get', 'rss_filter_feed');
}

function rss_filter_list_get()
{
   ploopi_ajaxloader('rss_filter_list');
   ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_filter_list_get', 'rss_filter_list');

   rss_filter_feed_get();
}

function rss_filter_list_choose(rssfilter_id)
{
   ploopi_innerHTML('rss_filter_list', ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_filter_list_get&rssfilter_id='+rssfilter_id));

   rss_filter_element_list_get();
   rss_filter_feed_get();
}

function rss_filter_element_list_get()
{
   ploopi_ajaxloader('rss_filter_element_list');
   ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_filter_element_list_get', 'rss_filter_element_list');
}

function rss_filter_element_list_choose(rssfilter_id_element)
{
   ploopi_innerHTML('rss_filter_element_list', ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_filter_element_list_get&rssfilter_id_element='+rssfilter_id_element));

   rss_filter_feed_get();
}

function rssfilter_element_list_delete(rssfilter_id_element)
{
   ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rssfilter_element_delete&rssfilter_id_element='+rssfilter_id_element);
   rss_filter_element_list_get();
   rss_filter_feed_get();
}

function rss_filter_element_edit_list_get(rssfilter_id_element)
{
   ploopi_innerHTML('rss_filter_element_list', ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_filter_element_edit_list_get&rssfilter_id_element='+rssfilter_id_element));
}

function rss_filter_element_edit(rssfilter_id_element)
{
   if(typeod(rssfilter_id_element) == 'undefined')
   {
      ploopi_innerHTML('rss_filter_element_edit', ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_filter_element_edit'));
   }
   else
   {
      ploopi_innerHTML('rss_filter_element_edit', ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rss_filter_element_edit&rssfilter_id_element='+rssfilter_id_element));
      rss_filter_element_edit_list_get(rssfilter_id_element);
   }
}

function rssfilter_element_edit_save(rssfilter_id_element)
{
   ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rssfilter_element_save&rssfilter_id_element='+rssfilter_id_element);
   rss_filter_element_edit_list_get(rssfilter_id_element);
}

function rssfilter_element_edit_delete(rssfilter_id_element)
{
   ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=rssfilter_element_delete&rssfilter_id_element='+rssfilter_id_element);
   rss_filter_element_edit_list_get(rssfilter_id_element);
}

