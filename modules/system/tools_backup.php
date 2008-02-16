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
shell_exec(_PLOOPI_MYSQLPATH."/bin/mysqldump -u"._PLOOPI_DB_LOGIN." -p"._PLOOPI_DB_PASSWORD." --opt --add-drop-table --complete-insert --quote-names "._PLOOPI_DB_DATABASE." | gzip > ./data/"._PLOOPI_DB_DATABASE.".sql.gz");
shell_exec("tar czf ./data/"._PLOOPI_DB_DATABASE.".tgz . --exclude=#* --exclude=*.tgz");
shell_exec("rm -rf ./data/"._PLOOPI_DB_DATABASE.".sql.gz");

if (!ploopi_downloadfile(realpath('./data/'), _PLOOPI_DB_DATABASE.'.tgz', true)) echo 'File Not Found !';
?>
