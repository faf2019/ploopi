function weathertools_open_bulletin(icao, e)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 450, e, false, 'popup_weathertools_bulletin');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=weathertools_open_bulletin&weathertools_icao='+icao,'popup_weathertools_bulletin');
}
