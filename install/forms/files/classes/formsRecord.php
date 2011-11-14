<?php
/*
    Copyright (c) 2007-2011 Ovensia
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

/**
 * Gestion des enregistrements
 *
 * @package forms
 * @subpackage reply
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusions
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à un enregistrement de formulaire
 *
 * @package forms
 * @subpackage record
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class formsRecord extends data_object
{
    public function __construct(&$objForm)
    {
        parent::__construct($objForm->getDataTableName(), '#id');
    }

    public function save()
    {
        if ($this->isnew())
        {
            $this->fields['date_validation'] = ploopi_createtimestamp();
            $this->fields['user_id'] = isset($_SESSION['ploopi']['user']['id']) ? $_SESSION['ploopi']['user']['id'] : '';
            $this->fields['user_login'] = isset($_SESSION['ploopi']['user']['login']) ? $_SESSION['ploopi']['user']['login'] : '';
            $this->fields['user_firstname'] = isset($_SESSION['ploopi']['user']['firstname']) ? $_SESSION['ploopi']['user']['firstname'] : '';
            $this->fields['user_lastname'] = isset($_SESSION['ploopi']['user']['lastname']) ? $_SESSION['ploopi']['user']['lastname'] : '';
            $this->fields['workspace_id'] = isset($_SESSION['ploopi']['workspaceid']) ? $_SESSION['ploopi']['workspaceid'] : '';
            $this->fields['workspace_label'] = isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label']) ? $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label'] : '';
            $this->fields['workspace_code'] = isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['code']) ? $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['code'] : '';
            $this->fields['ip'] = isset($_SESSION['ploopi']['remote_ip']) ? current($_SESSION['ploopi']['remote_ip']) : '';
        }

        // Si champ vide, affectation de null (permet de ne pas imposer une valeur par défaut notamment pour les champs numériques pour lesquels une chaine vide est interprétée en 0)
        foreach($this->fields as $strKey => $strVal) if ($strVal == '') $this->fields[$strKey] = null;

        return parent::save();
    }
}
?>
