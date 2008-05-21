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

/**
 * Gestion des annotations
 * 
 * @package ploopi
 * @subpackage annotation
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_annotation
 * 
 * @package ploopi
 * @subpackage annotation
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class annotation extends data_object
{

    /**
     * Constructeur de la classe
     *
     * @return annotation
     */
    
    function annotation()
    {
        parent::data_object('ploopi_annotation','id');
    }

    /**
     * Enregistre l'annotation et les tags associés
     *
     * @return int identifiant de l'annotation
     */
    
    function save()
    {
        global $db;

        $this->fields['id_element'] = ploopi_search_generate_id($this->fields['id_module'], $this->fields['id_object'], $this->fields['id_record']);

        $id_annotation = parent::save();

        $tags = preg_split('/(,)|( )/',$this->tags,-1,PREG_SPLIT_NO_EMPTY);
        foreach($tags as $tag)
        {
            $tag = trim($tag);

            $tag_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($tag));

            $select = "SELECT id FROM ploopi_tag WHERE tag = '".$db->addslashes($tag)."' AND id_user = {$this->fields['id_user']}";
            $rs = $db->query($select);
            if (!($row = $db->fetchrow($rs)))
            {
                $objtag = new tag();
                $objtag->fields['tag'] = $tag;
                $objtag->fields['tag_clean'] = $tag_clean;
                $objtag->fields['id_user'] = $this->fields['id_user'];
                $id_tag = $objtag->save();
            }
            else $id_tag = $row['id'];

            $annotation_tag = new annotation_tag();
            $annotation_tag->fields['id_tag'] = $id_tag;
            $annotation_tag->fields['id_annotation'] = $id_annotation;
            $annotation_tag->save();
        }

        return($id_annotation);
    }


    /**
     * Supprime l'annotation
     */
    
    function delete()
    {
        global $db;

        $select =   "
                    SELECT  *
                    FROM    ploopi_annotation_tag
                    WHERE   id_annotation = {$this->fields['id']}
                    ";

        $rs = $db->query($select);
        while ($row = $db->fetchrow($rs))
        {
            $annotation_tag = new annotation_tag();
            $annotation_tag->open($this->fields['id'], $row['id_tag']);
            $annotation_tag->delete();
        }

        parent::delete();
    }
}

/**
 * Classe d'accès à la table ploopi_annotation_tag
 * 
 * @package ploopi
 * @subpackage annotation
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class annotation_tag extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return annotation_tag
     */
    
    function annotation_tag()
    {
        parent::data_object('ploopi_annotation_tag','id_annotation','id_tag');
    }

    /**
     * Supprime le tag s'il n'est plus utilisé
     */
    
    function delete()
    {
        global $db;

        $select =   "
                    SELECT  count(*) as c
                    FROM    ploopi_annotation_tag
                    WHERE   id_tag = {$this->fields['id_tag']}
                    AND     id_annotation <> {$this->fields['id_annotation']}
                    ";

        $rs = $db->query($select);
        if (!($row = $db->fetchrow($rs)) || $row['c'] == 0)
        {
            $tag = new tag();
            $tag->open($this->fields['id_tag']);
            $tag->delete();
        }

        parent::delete();
    }

}

/**
 * Classe d'accès à la table ploopi_tag
 *  
 * @package ploopi
 * @subpackage annotation
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class tag extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return tag
     */
    
    function tag()
    {
        parent::data_object('ploopi_tag','id');
    }
}
?>
