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

// NEEDS PEAR PACKAGE
@include_once 'Cache/Lite/Output.php';

global $ploopi_cache_activated;
global $ploopi_cache_written;
global $ploopi_cache_read;

$ploopi_cache_activated = $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_set_cache'] && !_PLOOPI_DEBUGMODE && class_exists('Cache_Lite_Output');
$ploopi_cache_written = 0;
$ploopi_cache_read = 0;

class ploopi_cache  extends Cache_Lite_Output
{
    var $cache_id;

    function ploopi_cache($id, $lifetime = _PLOOPI_CACHE_DEFAULT_LIFETIME)
    {
        global $ploopi_cache_activated;

        if ($ploopi_cache_activated)
        {
            $this->cache_id = $id;
            $this->Cache_Lite_Output(array( 'cacheDir' => '/tmp/', 'lifeTime' => $lifetime));
        }
    }

    function get_lastmodified()
    {
        global $ploopi_cache_activated;

        if ($ploopi_cache_activated)
        {
            $this->_setFileName($this->cache_id, 'default');
            if (file_exists($this->_file)) return($this->lastModified());
            else return(0);
        }

        return(0);
    }

    function start($force_caching = false)
    {
        global $ploopi_cache_activated;
        global $ploopi_cache_written;
        global $ploopi_cache_read;

        if ($ploopi_cache_activated)
        {
            if ($force_caching) $this->setOption('lifeTime', 0);
            $cache_content = parent::start($this->cache_id);

            if ($cache_content) $ploopi_cache_read++;
            else $ploopi_cache_written++;

            return($cache_content);
        }
        else return(false); // no cache

    }

    function end()
    {
        global $ploopi_cache_activated;

        if ($ploopi_cache_activated)
        {
            parent::end();
        }
    }
}

?>
