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

class ploopi_session
{
    function open() {}

    function close() {}

    function read($id)
    {
        global $db;
        return ($db->query("SELECT data FROM ploopi_session WHERE id = '".$db->addslashes($id)."'") && $record = $db->fetchrow()) ? gzuncompress($record['data']) : '';
    }

    function write($id, $data)
    {
        global $db;
        return $db->query("REPLACE INTO ploopi_session VALUES ('".$db->addslashes($id)."', '".$db->addslashes(time())."', '".$db->addslashes(gzcompress($data))."')");
    }

    function destroy($id)
    {
        global $db;
        return $db->query("DELETE FROM ploopi_session WHERE id = '".$db->addslashes($id)."'");
    }

    function gc($max)
    {
        global $db;
        $old = time() - $max;
        return $db->query("DELETE FROM ploopi_session WHERE access < '".$db->addslashes($old)."'");
    }
}
?>
