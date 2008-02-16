<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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

ploopi_init_module('rss');

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

$tabs['tabExplorer'] = array (  'title' => _RSS_LABEL_FEEDEXPLORER,
                                'url'   => "{$scriptenv}?rssTabItem=tabExplorer"
                            );

if ($_SESSION['ploopi']['connected'])
{
    $tabs['tabSearches'] = array (  'title' => _RSS_LABEL_FEEDSEARCHES,
                                    'url'   => "{$scriptenv}?rssTabItem=tabSearches"
                                );
}

if (!empty($_GET['rssTabItem'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'] = $_GET['rssTabItem'];
if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'] = '';

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
echo $skin->create_tabs('',$tabs,$_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem']);

switch($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'])
{
    case 'tabExplorer':
        include('./modules/rss/public_explorer.php');
    break;

    case 'tabSearches':
        switch($op)
        {
            case 'delete_request':
                $rssrequest = new rssrequest();
                $rssrequest->open($rssrequest_id);
                $rssrequest->delete();
                ploopi_redirect("$scriptenv");
            break;

            case 'save_request':
                $rssrequest = new rssrequest();
                if ($rssrequest_request1 != '')
                {
                    $rssrequest->fields['request'] = $rssrequest_request1;
                }
                if ($rssrequest_request2 != '')
                {
                    $rssrequest->fields['request'] .= " <$rssrequest_OP2> $rssrequest_request2";
                }
                if ($rssrequest_request3 != '')
                {
                    $rssrequest->fields['request'] .= " <$rssrequest_OP3> $rssrequest_request3";
                }

                $rssrequest->fields['request'] = trim($rssrequest->fields['request'],' ');
                $rssrequest->fields['id_cat'] = $rssrequest_id_cat;

                $rssrequest = ploopi_setugm($rssrequest);
                $rssrequest->save();
                ploopi_redirect("$scriptenv?op=show_request&rssrequest_id={$rssrequest->fields['id']}");
            break;

            default:
                include('./modules/rss/rss_searches.php');
            break;
        }
    break;

}
?>
