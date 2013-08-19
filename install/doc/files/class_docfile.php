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
 * Gestion des fichiers
 *
 * @package doc
 * @subpackage file
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Inclusion de la classe docfolder.
 */

include_once './modules/doc/class_docfolder.php';

include_once './include/functions/filesystem.php';


/**
 * Classe d'accès à la table ploopi_mod_doc_file.
 * Gère l'enregistrement physique, l'extraction du contenu, l'indexation, la suppression.
 *
 * @package doc
 * @subpackage file
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class docfile extends data_object
{
    var $oldname;
    var $tmpfile;
    var $draftfile;
    var $sharedfile;

    /**
     * Constructeur de la classe
     *
     * @return docfile
     */

    function docfile()
    {
        parent::data_object('ploopi_mod_doc_file');
        $this->fields['id_user'] = 0;
        $this->fields['timestp_create'] = ploopi_createtimestamp();
        $this->fields['timestp_modify'] = $this->fields['timestp_create'];
        $this->fields['description']='';
        $this->fields['size'] = 0;
        $this->fields['version'] = 1;
        $this->fields['nbclick'] = 0;

        $this->oldname = '';
        $this->tmpfile = null;
        $this->sharedfile = null;
        $this->draftfile = null;
    }

    /**
     * Ouvre un document
     *
     * @param unknown_type $id
     * @return unknown
     */

    function open($id)
    {
        $res = parent::open($id);
        $this->oldname = $this->fields['name'];
        return $res;
    }

    /**
     * Ouvre un document avec son identifiant MD5
     *
     * @param string $md5id identifiant MD5 du document
     * @return boolean true si le document a été ouvert
     */

    function openmd5($md5id)
    {
        global $db;

        $db->query("SELECT id FROM ploopi_mod_doc_file WHERE md5id = '".$db->addslashes($md5id)."'");
        if ($fields = $db->fetchrow()) return($this->open($fields['id']));
        else return false;
    }

    /**
     * Enregistre le document
     *
     * @return int numéro d'erreur
     *
     * @see _DOC_ERROR_EMPTYFILE
     * @see _DOC_ERROR_FILENOTWRITABLE
     * @see _DOC_ERROR_MAXFILESIZE
     * @see _PLOOPI_MAXFILESIZE
     */

    function save()
    {
        global $db;

        $booParse = false;

        $error = 0;
        if (isset($this->fields['folder'])) unset($this->fields['folder']);

        if (!isset($this->oldname)) $this->oldname = '';

        if ($this->isnew()) // insert
        {
            if ($this->tmpfile == 'none' && $this->draftfile == 'none' && $this->sharedfile == null) $error = _DOC_ERROR_EMPTYFILE;

            if ($this->fields['size'] > _PLOOPI_MAXFILESIZE && $this->sharedfile == null) $error = _DOC_ERROR_MAXFILESIZE;

            if (!$error)
            {
                $this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

                $id = parent::save();

                $this->fields['md5id'] = md5(sprintf("%s_%d_%d", $this->fields['timestp_create'], $id, $this->fields['version']));

                parent::save();

                $basepath = $this->getbasepath();
                $filepath = $this->getfilepath();

                if (file_exists($filepath) && !is_writable($filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;

                if (!$error && is_writable($basepath))
                {
                    if ($this->sharedfile != null)
                    {
                        if (!copy($this->sharedfile, $filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;
                    }
                    elseif ($this->draftfile != null)
                    {
                        if (!rename($this->draftfile, $filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;
                    }
                    elseif ($this->tmpfile != null)
                    {
                        if (!rename($this->tmpfile, $filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;
                    }

                    if (!$error)
                    {
                        //$this->parse();
                        $booParse = true;
                        chmod($filepath, 0640);
                    }
                }
                else $error = _DOC_ERROR_FILENOTWRITABLE;
            }
        }
        else // update
        {

            if ((!empty($this->tmpfile) && $this->tmpfile != 'none') || (!empty($this->draftfile) && $this->draftfile != 'none')  || (!empty($this->sharedfile)))
            {
                $this->fields['version']++;

                if ($this->fields['size']>_PLOOPI_MAXFILESIZE) $error = _DOC_ERROR_MAXFILESIZE;

                if (!$error)
                {
                    $this->fields['extension'] = substr(strrchr($this->fields['name'], "."),1);

                    $basepath = $this->getbasepath();
                    $filepath = $this->getfilepath();

                    //$filepath_vers = $basepath._PLOOPI_SEP."{$this->fields['id']}_{$this->fields['version']}.{$this->fields['extension']}";

                    if (file_exists($filepath) && !is_writable($filepath)) $error = _DOC_ERROR_FILENOTWRITABLE;

                    if (!$error)
                    {
                        // on déplace l'ancien fichier
                        /*
                        if (file_exists($filepath) && is_writable($basepath))
                        {
                            rename($filepath, $filepath_vers);
                            //$this->createhistory();
                        }
                        */

                        // on copie le nouveau
                        if (!$error && is_writable($basepath))
                        {
                            if ($this->sharedfile != null)
                            {
                                if (copy($this->sharedfile, $filepath))
                                {
                                    $booParse = true;
                                    //$this->parse();
                                    chmod($filepath, 0640);
                                }
                                else $error = _DOC_ERROR_FILENOTWRITABLE;
                            }
                            if ($this->draftfile != null)
                            {
                                if (rename($this->draftfile, $filepath))
                                {
                                    $booParse = true;
                                    //$this->parse();
                                    chmod($filepath, 0640);
                                }
                                else $error = _DOC_ERROR_FILENOTWRITABLE;
                            }
                            elseif ($this->tmpfile != null)
                            {
                                if (rename($this->tmpfile, $filepath))
                                {
                                    $booParse = true;
                                    //$this->parse();
                                    chmod($filepath, 0640);
                                }
                                else $error = _DOC_ERROR_FILENOTWRITABLE;
                            }
                        }
                        else $error = _DOC_ERROR_FILENOTWRITABLE;
                    }
                }

                $this->fields['timestp_modify'] = ploopi_createtimestamp();

                $this->oldname = $this->fields['name'];
            }

            // renommage
            if ($this->oldname != $this->fields['name'])
            {
                $booParse = true;

                // renommage avec modification de type
                if (($newext = substr(strrchr($this->fields['name'], "."),1)) != $this->fields['extension'])
                {
                    $basepath = $this->getbasepath();
                    $filepath = $this->getfilepath();
                    $newfilepath = substr($filepath,0,strlen($filepath)-strlen($this->fields['extension'])).$newext;

                    if (file_exists($filepath) && is_writable($basepath))
                    {
                        rename($filepath, $newfilepath);
                        $this->fields['extension'] = $newext;
                        parent::save();
                    }
                    else $error = _DOC_ERROR_FILENOTWRITABLE;
                }
                else
                {
                    parent::save();
                }
            }
            else
            {
                parent::save();
            }
        }

        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new docfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $docfolder_parent->fields['nbelements'] = doc_countelements($this->fields['id_folder']);
            $docfolder_parent->save();
        }

        if ($booParse) $this->parse();

        return($error);
    }

    /**
     * Supprime le document/fichier
     */

    function delete()
    {
        global $db;

        $filepath = $this->getfilepath();
        if (file_exists($filepath)) @unlink($filepath);

        $basepath = $this->getbasepath();
        if (file_exists($basepath))
        {
            $dh = opendir($basepath);
            $booEmptyDir = true;
            while (($file = readdir($dh)) !== false) $booEmptyDir = $booEmptyDir && in_array($file, array('.', '..'));

            // Pas de sous dossiers ou de fichiers, on peut effacer le dossier
            if ($booEmptyDir) @rmdir($basepath);
        }

        ploopi_search_remove_index(_DOC_OBJECT_FILE, $this->fields['md5id']);

        // delete existing meta for current file
        $db->query("DELETE FROM ploopi_mod_doc_meta WHERE id_file = {$this->fields['id']}");

        parent::delete();

        if ($this->fields['id_folder'] != 0)
        {
            $docfolder_parent = new docfolder();
            $docfolder_parent->open($this->fields['id_folder']);
            $docfolder_parent->fields['nbelements'] = doc_countelements($this->fields['id_folder'], $this->fields['id_module']);
            $docfolder_parent->save();
        }
    }

    /**
     * Déplace un fichier vers un brouillon
     */

    function movetodraft()
    {
        // Création d'un draft à partir du fichier d'origine
        $docfiledraft = new docfiledraft();

        foreach(array('name', 'description', 'timestp_create', 'size', 'readonly', 'extension', 'id_folder', 'id_user_modify', 'id_user', 'id_workspace', 'id_module') as $key)
            $docfiledraft->fields[$key] = $this->fields[$key];

        $docfiledraft->tmpfile = $this->getfilepath();
        $docfiledraft->save();

        $this->delete();

        return $docfiledraft;
    }

    /**
     * Retourne le chemin physique de stockage des documents et le crée s'il n'existe pas
     *
     * @return string chemin physique de stockage des documents
     *
     * @see doc_getpath
     */

    function getbasepath()
    {
        $basepath = doc_getpath($this->fields['id_module'])._PLOOPI_SEP.substr($this->fields['timestp_create'],0,8);
        ploopi_makedir($basepath);
        return($basepath);
    }

    /**
     * Retourne le chemin physique de stockage du document
     *
     * @return string chemin physique de stockage du document
     */

    function getfilepath()
    {
        return($this->getbasepath()._PLOOPI_SEP."{$this->fields['id']}_{$this->fields['version']}.{$this->fields['extension']}");
    }


    /**
     * Retourne l'historique d'un fichier dans un tableau
     *
     * @return array historique d'un fichier indexé par version
     */

    function gethistory()
    {
        global $db;

        $rs = $db->query(   "
                            SELECT      h.*,
                                        f.md5id,
                                        u.login,
                                        u.firstname,
                                        u.lastname

                            FROM        ploopi_mod_doc_file_history h

                            INNER JOIN  ploopi_mod_doc_file f
                            ON          h.id_docfile = f.id

                            INNER JOIN  ploopi_user u
                            ON          h.id_user_modify = u.id

                            WHERE       h.id_docfile = {$this->fields['id']}

                            ORDER BY    h.version DESC
                            ");

        $history = array();

        while($row = $db->fetchrow($rs))
        {
            $history[$row['version']] = $row;
        }

        return($history);
    }

    /**
     * Crée un historique à partir de ce document
     */

    function createhistory()
    {
        include_once './modules/doc/class_docfilehistory.php';

        $docfilehistory = new docfilehistory();
        $docfilehistory->fields['id_docfile'] = $this->fields['id'];
        $docfilehistory->fields['version'] = $this->fields['version'];
        $docfilehistory->fields['name'] = $this->fields['name'];
        $docfilehistory->fields['description'] = $this->fields['description'];
        $docfilehistory->fields['timestp_create'] = $this->fields['timestp_create'];
        $docfilehistory->fields['timestp_modify'] = $this->fields['timestp_modify'];
        $docfilehistory->fields['id_user_modify'] = $this->fields['id_user_modify'];
        $docfilehistory->fields['size'] = $this->fields['size'];
        $docfilehistory->fields['extension'] = $this->fields['extension'];
        $docfilehistory->fields['id_module'] = $this->fields['id_module'];
        $docfilehistory->save();
    }

    /**
     * Retourne les META d'un fichier dans un tableau
     *
     * @return array meta d'un fichier
     */

    function getmeta()
    {
        global $db;

        $rs = $db->query(   "
                            SELECT      m.*
                            FROM        ploopi_mod_doc_meta m
                            WHERE       m.id_file = {$this->fields['id']}
                            ORDER BY    m.meta
                            ");

        if($db->numrows($rs)) return $db->getarray($rs, true);

        return array();
    }

    /**
     * Indexe le document
     *
     * @param boolean $debug true si on veut afficher des informations de debug
     * @return unknown
     *
     * @see docmeta
     * @see _DOC_OBJECT_FILE
     * @see ploopi_search_create_index
     */

    function parse($debug = false)
    {
        include_once './modules/doc/class_docmeta.php';

        global $db;

        global $ploopi_timer;
        if ($debug) printf("<br />START: %0.2f",$ploopi_timer->getexectime()*1000);

        if (!ini_get('safe_mode')) @set_time_limit(0);

        $metakeywords_str = '';

        $allowedmeta_list =
            array(
                'Camera Make',      //jpg
                'Camera Model',     //jpg
                'Comment',          //jpg
                'Producer',         //png
                'Creator',          //pdf,png
                'Author',           //doc
                'Title'             //pdf
            );

        $res_txt = '';

        // on recherche les parsers adaptés au format du fichier
        $sql =  "
                select      lcase(f.extension) as ext,
                            p.path
                from        ploopi_mod_doc_file f
                left join   ploopi_mod_doc_parser p on lcase(f.extension) = lcase(p.extension)
                where       f.id = {$this->fields['id']}
                ";

        $res = $db->query($sql);

        $fields = $db->fetchrow($res);

        $path = $this->getfilepath();
        if (file_exists($path))
        {
            /* GESTION/EXTRACTION DES METADONNEES */

            $this->fields['metadata'] = '';

            switch($fields['ext'])
            {
                case 'pdf':
                    $exec = "pdfinfo \"{$path}\"";
                break;

                case 'jpg':
                case 'jpeg':
                    $exec = "jhead \"{$path}\"";
                break;

                default:
                    $exec = "hachoir-metadata --quiet --raw \"{$path}\"";
                break;
            }

            $res_txt = "<div style=\"background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;padding:1px;margin-top:2px;\"><b>{$this->fields['name']}</b> : {$exec}</div>\n";

            exec($exec,$array_result);
            if ($debug) printf("<br />META: %0.2f",$ploopi_timer->getexectime()*1000);

            // delete existing meta for current file
            $db->query("DELETE FROM ploopi_mod_doc_meta WHERE id_file = {$this->fields['id']}");

            // parse doc metadata
            foreach($array_result as $value)
            {
                if ($value!="")
                {
                    foreach(preg_split("/\n/",$value) as $line)
                    {
                        unset($meta_information);

                        switch($fields['ext'])
                        {
                            case 'pdf':
                                preg_match("/([A-Za-z0-9_. ]*):(.*)/", $value, $meta_information);
                            break;

                            case 'jpg':
                            case 'jpeg':
                                preg_match("/([A-Za-z0-9_. ]*) : (.*)/", $value, $meta_information);
                            break;

                            default:
                                if ($value != 'Metadata:') preg_match("/- ([A-Za-z0-9_. ]*): (.*)/", $value, $meta_information);
                            break;
                        }

                        if (!empty($meta_information))
                        {
                            $res_txt .= "<div style=\"background-color:#e0f0e0;border-bottom:1px solid #c0c0c0;padding:1px;\"><b>{$meta_information['1']}</b> = {$meta_information['2']}</div>\n";

                            $docmeta = new docmeta();
                            $docmeta->fields['id_file'] = $this->fields['id'];
                            $docmeta->fields['meta'] = trim(ucwords(str_replace('_',' ',$meta_information['1'])));
                            $docmeta->fields['value'] = trim($meta_information['2']);
                            $docmeta->save();

                            if (in_array($docmeta->fields['meta'],$allowedmeta_list)) $metakeywords_str .= ' '.$docmeta->fields['value'];
                        }
                    }
                    //$this->fields['metadata'] .= strtolower($value)."\n";
                    $this->fields['metadata'] .= $value."\n";
                }

            }
            unset($array_result);

            if ($debug) printf("<br />META 2: %0.2f",$ploopi_timer->getexectime()*1000);

            /* EXTRACTION DES CONTENUS */

            $content = '';

            if (!is_null($fields['path']))
            {
                // parse doc content
                $exec = str_replace('%f',"\"{$path}\"",$fields['path']);

                $res_txt .= "<div style=\"background-color:#ffe0e0;border-bottom:1px solid #c0c0c0;padding:1px;margin-top:2px;\">{$exec}</div>\n";

                exec($exec,$array_result);
                if ($debug) printf("<br />CONTENT: %0.2f",$ploopi_timer->getexectime()*1000);

                foreach($array_result as $key => $value)
                {
                    if ($value!="")
                    {
                        switch($fields['ext'])
                        {
                            case 'odg':
                            case 'odt':
                            case 'ods':
                            case 'odp':
                            case 'sxw':
                                $value = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value);
                            break;
                        }
                        $content .= $value.' ';
                    }
                }

                unset($array_result);
            }

            $res_txt .= "<div style=\"background-color:#e0e0f0;border-bottom:1px solid #c0c0c0;padding:1px;\">".ploopi_strcut($content,200)."</div>\n";

            $metakeywords_str .= " {$this->fields['name']} {$this->fields['description']}";

            ploopi_search_remove_index(_DOC_OBJECT_FILE, $this->fields['md5id'], $this->fields['id_module']);
            ploopi_search_create_index(
                _DOC_OBJECT_FILE,
                $this->fields['md5id'],
                $this->fields['name'],
                $content,
                $metakeywords_str,
                true,
                $this->fields['timestp_create'],
                $this->fields['timestp_modify'],
                $this->fields['id_user'],
                $this->fields['id_workspace'],
                $this->fields['id_module']
            );

        }
        else $res_txt .= "<div><strong>erreur de fichier sur {$path}</strong></div>\n";

        unset($content);
        unset($metakeywords_str);

        return($res_txt);
    }
}
