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

class docfilehistory extends data_object
{
    function docfilehistory()
    {
        parent::data_object('ploopi_mod_doc_file_history', 'id_docfile', 'version');
    }

    /*function getbasepath()
    {
        $basepath = doc_getpath($this->fields['id_module'])._PLOOPI_SEP.$this->fields['id_docfile'];
        ploopi_makedir($basepath);
        return($basepath);
    }

    function getfilepath()
    {
        return($this->getbasepath()._PLOOPI_SEP."{$this->fields['id_docfile']}_{$this->fields['version']}.{$this->fields['extension']}");
    }*/

    function getbasepath()
    {
        $basepath = doc_getpath($this->fields['id_module'])._PLOOPI_SEP.substr($this->fields['timestp_create'],0,8);
        ploopi_makedir($basepath);
        return($basepath);
    }

    function getfilepath()
    {
        return($this->getbasepath()._PLOOPI_SEP."{$this->fields['id_docfile']}_{$this->fields['version']}.{$this->fields['extension']}");
    }
}
