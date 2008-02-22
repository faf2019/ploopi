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
?>
<?

function ploopi_cache_start($id, $lifetime = _PLOOPI_CACHE_DEFAULT_LIFETIME)
{

    if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_set_cache'])
    {
        global $ploopi_cache_output;
        include_once 'Cache/Lite/Output.php';

        $id .= "_{$_SESSION['ploopi']['workspaceid']}";

        // add userid for connected users
        if ($_SESSION['ploopi']['connected']) $id .= "_{$_SESSION['ploopi']['userid']}";

        $options = array(
            'cacheDir' => '/tmp/',
            'lifeTime' => $lifetime
            );

        $ploopi_cache_output = new Cache_Lite_Output($options);

        return($ploopi_cache_output->start($id));
    }
    else return(false); // no cache

}

function ploopi_cache_end()
{
    if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_set_cache'])
    {
        global $ploopi_cache_output;
        $ploopi_cache_output->end();
    }
}

?>