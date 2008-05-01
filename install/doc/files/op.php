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

if ($_SESSION['ploopi']['connected'])
{
    // on verifie qu'on est bien dans le module DOC
    if (ploopi_ismoduleallowed('doc'))
    {
        switch($ploopi_op)
        {
            case 'doc_getstatus':
                if (substr(_PLOOPI_CGI_UPLOADTMP, -1, 1) != '/') define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP.'/');
                else define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP);
                include_once './lib/cupload/status.php';
                ploopi_die();
            break;
    
            case 'doc_help':
                include_once './modules/doc/public_legend.php';
                ploopi_die();
            break;
    
            case 'doc_search_next':
                ploopi_init_module('doc');
                include_once './modules/doc/public_search_result.php';
                ploopi_die();
            break;
        }
    }

    // public
    
    switch($ploopi_op)
    {
        case 'doc_getfiles':
            if (!empty($_GET['idfolder']) && is_numeric($_GET['idfolder']) && isset($_GET['filter']))
            {
                switch($_GET['filter'])
                {
                    case 'doc_selectimage':
                        $filter_ext = array('jpg', 'gif', 'png', 'bmp');
                    break;
    
                    case 'doc_selectflash':
                        $filter_ext = array('swf');
                    break;
    
                    default:
                        $filter_ext = array();
                    break;
                }
    
                // on cherche les fichiers du dossier "idfolder"
                // en vérifiant au passage que le dossier est accessible par l'espace courant
                // et qu'il est public.
    
                $sql =  "
                        SELECT      doc.md5id,
                                    doc.name,
                                    doc.size
    
                        FROM        ploopi_mod_doc_file doc
    
                        INNER JOIN  ploopi_mod_doc_folder folder
                        ON          folder.id = doc.id_folder
    
                        WHERE       doc.id_folder = {$_GET['idfolder']}
                        AND         folder.id_workspace = {$_SESSION['ploopi']['workspaceid']}
                        AND         folder.foldertype = 'public'
    
                        ORDER BY    doc.name
                        ";
    
                // exec requete + encodage JSON
                $db->query($sql);
    
                $files = array();
                while ($row = $db->fetchrow())
                {
                    if (empty($filter_ext) || in_array(ploopi_file_getextension($row['name']),$filter_ext)) $files[] = $row;
                }
    
                ploopi_print_json($files);
            }
    
            ploopi_die();
        break;
    
    
        case 'doc_selectfile':
        case 'doc_selectimage':
        case 'doc_selectflash':
            ob_start();
            include_once './modules/doc/fck_explorer.php';
            $main_content = ob_get_contents();
            @ob_end_clean();
    
            $template_body->assign_vars(array(
                'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
                'ADDITIONAL_JAVASCRIPT' => $additional_javascript,
                'PAGE_CONTENT'          => $main_content
                )
            );
    
            $template_body->pparse('body');
            ploopi_die();
        break;
    
        case 'doc_image_get':
            include_once './include/classes/class_data_object.php';
            include_once './include/functions/date.php';
            include_once './include/functions/filesystem.php';
            include_once './include/functions/image.php';
            include_once './modules/doc/include/global.php';
            include_once './modules/doc/class_docfile.php';
    
            if (!empty($_GET['docfile_id']))
            {
                $docfile = new docfile();
                $docfile->open($_GET['docfile_id']);
            }
    
            if (!empty($_GET['docfile_md5id']))
            {
                $db->query("SELECT id FROM ploopi_mod_doc_file WHERE md5id = '".$db->addslashes($_GET['docfile_md5id'])."'");
                if ($fields = $db->fetchrow())
                {
                    $docfile = new docfile();
                    $docfile->open($fields['id']);
                }
            }
    
            if (!empty($docfile))
            {
                $height = (isset($_GET['height'])) ? $_GET['height'] : 0;
                $width = (isset($_GET['width'])) ? $_GET['width'] : 0;
                $coef = (isset($_GET['coef'])) ? $_GET['coef'] : 0;
    
                if (file_exists($docfile->getfilepath())) ploopi_resizeimage($docfile->getfilepath(), $coef, $width, $height);
            }
        break;
    }
}


switch($ploopi_op)
{
    case 'doc_file_download':
        include_once './include/global_constants.php';
        include_once './include/classes/class_data_object.php';
        include_once './include/functions/date.php';
        include_once './include/functions/filesystem.php';
        include_once './modules/doc/include/global.php';
        include_once './modules/doc/class_docfile.php';

        if (!empty($_GET['docfile_id']))
        {
            $docfile = new docfile();
            $docfile->open($_GET['docfile_id']);

            if (file_exists($docfile->getfilepath())) ploopi_downloadfile($docfile->getfilepath(),$docfile->fields['name']);
            else if (file_exists($docfile->getfilepath_deprecated())) ploopi_downloadfile($docfile->getfilepath_deprecated(),$docfile->fields['name']);
        }

        if (!empty($_GET['docfile_md5id']))
        {
            $db->query("SELECT id FROM ploopi_mod_doc_file WHERE md5id = '".$db->addslashes($_GET['docfile_md5id'])."'");
            if ($fields = $db->fetchrow())
            {
                $docfile = new docfile();
                $docfile->open($fields['id']);
                if (file_exists($docfile->getfilepath())) ploopi_downloadfile($docfile->getfilepath(),$docfile->fields['name']);
                else if (file_exists($docfile->getfilepath_deprecated())) ploopi_downloadfile($docfile->getfilepath_deprecated(),$docfile->fields['name']);
            }
        }

        ploopi_die();
    break;
}

?>
