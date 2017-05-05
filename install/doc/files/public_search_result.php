<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2012 Ovensia
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
 * Interface de résultat du moteur de recherche
 *
 * @package doc
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see doc_getshare
 * @see ploopi_search
 */

/**
 * Récupération des paramètres de recherche et remplissage de la variable session du module
 * Initialisation de la variable session si elle n'est pas définie
 */

foreach(array('keywords', 'filetype', 'user', 'workspace', 'date1', 'date2', 'stem', 'phonetic', 'and') as $p) {
    $param = 'doc_search_'.$p;
    if (isset($_GET[$param]))    $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_'.$p] = $_GET[$param];
    if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_'.$p])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_'.$p] = '';
}

/**
 * On démarre la recherche si au moins un mot clé a été saisi
 */
if (isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_keywords']))
{
    /**
     * Charge les validations
     */

    doc_getvalidation();

    /**
     * Charge les partages
     */

    doc_getshare();

    $where = (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['files'])) ? ' OR f.id IN ('.implode(',', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['files']).')' : '';

    $search = array();
    $arrRelevance = array();

    if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_keywords']))
    {
        $arrOptions = array(
            'stem' => !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_stem']),
            'phonetic' => !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_phonetic']),
            'and' => !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_and'])
        );

        /**
         * Appel de la fonction de recherche du moteur d'indexation interne.
         * Renvoie une liste de fichiers correspondants au mots clés.
         */

        $arrRelevance = ploopi_search($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_keywords'], _DOC_OBJECT_FILE, '', $_SESSION['ploopi']['moduleid'], $arrOptions);
    }

    /**
     * Construction de la requête SQL de recherche avec les champs spécifiques du module DOC
     */

    if (!empty($arrRelevance)) $search[] = " f.md5id IN ('".implode("','",array_keys($arrRelevance))."') ";

    if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_name'])) $search[] = " f.name LIKE '%".$db->addslashes(trim($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_name']))."%' ";
    if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype'])) $search[] = " e.filetype LIKE '%".$db->addslashes($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype'])."%' ";
    if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user'])) $search[] = " u.login LIKE '%".$db->addslashes(trim($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user']))."%' ";
    if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace'])) $search[] = " w.label LIKE '%".$db->addslashes(trim($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace']))."%' ";

    if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1']) && !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2'])) $search[] = " f.timestp_modify BETWEEN '".ploopi_local2timestamp($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1'])."' AND '".ploopi_timestamp_add(ploopi_local2timestamp($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2']),0,0,0,0,1)."'";
    else
    {
        if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1'])) $search[] = " f.timestp_modify >= '".ploopi_local2timestamp($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1'])."' ";
        if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2'])) $search[] = " f.timestp_modify < '".ploopi_timestamp_add(ploopi_local2timestamp($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2']),0,0,0,0,1)."' ";
    }

    if (empty($search))
    {
        ?>
        <div style="padding:4px;font-weight:bold;background-color:#f0f0f0;border-bottom:1px solid #c0c0c0;">Saisissez un mot clé puis cliquez sur "Rechercher" ou appuyez sur "Entrée"</div>
        <?php
    }
    else
    {
        // Tableau pour construire la clause WHERE
        $arrWhere = array();

        // Module
        $arrWhere['module'] = "f.id_module = {$_SESSION['ploopi']['moduleid']}";

        // Utilisateur "standard"
        if (!ploopi_isadmin() && !ploopi_isactionallowed(_DOC_ACTION_ADMIN))
        {
            // Publié (ou propriétaire)
            $arrWhere['published'] = "(f.published = 1 OR f.id_user = {$_SESSION['ploopi']['userid']})";

            // Prioriétaire
            $arrWhere['visibility']['user'] = "f.id_user = {$_SESSION['ploopi']['userid']}";
            // Partagé
            if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])) $arrWhere['visibility']['shared'] = "(f.foldertype = 'shared' AND f.id IN (".implode(',', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['share']['folders'])."))";
            // Public
            $arrWhere['visibility']['public'] = "(f.foldertype = 'public' AND f.id_workspace IN (".ploopi_viewworkspaces()."))";

            // Validateur
            if (!empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']))
            {
                $arrWhere['visibility']['validator'] = "f.waiting_validation IN (".implode(',', $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['validation']['folders']).")";
            }

            // Synthèse visibilité
            $arrWhere['visibility'] = '('.implode(' OR ', $arrWhere['visibility']).')';
        }

        $strWhere = implode(' AND ', $arrWhere);

        $sql = "
            SELECT  f.*

            FROM    ploopi_mod_doc_folder f

            WHERE   {$strWhere}
        ";

        $db->query($sql);

        $arrFolderList = array();
        $arrFolderList[] = 0;
        while ($row = $db->fetchrow()) $arrFolderList[] = $row['id'];

        // Tableau pour construire la clause WHERE
        $arrWhere = array();

        // Module
        $arrWhere['module'] = "f.id_module = {$_SESSION['ploopi']['moduleid']}";

        // Folders
        $arrWhere['folders'] = "f.id_folder IN (".implode(',',$arrFolderList).")";

        // Search
        $arrWhere['search'] = implode(' AND ',$search);

        // Utilisateur "standard"
        if (!ploopi_isadmin() && !ploopi_isactionallowed(_DOC_ACTION_ADMIN))
        {
            // Dossier racine = cas particulier, l'utilisateur standard ne peut voir que sa racine
            $arrWhere['root'] = "(f.id_folder <> 0 OR (f.id_folder = 0 AND f.id_user = {$_SESSION['ploopi']['userid']}))";
        }

        $strWhere = implode(' AND ', $arrWhere);

        $sql =  "
                SELECT      f.*,
                            u.id as user_id,
                            u.login,
                            w.label,
                            e.filetype,
                            fd.foldertype,
                            fd.readonly as folder_readonly,
                            fd.id_user as fd_id_user,
                            fd.name as fd_name

                FROM        ploopi_mod_doc_file f

                LEFT JOIN   ploopi_mod_doc_folder fd
                ON          fd.id = f.id_folder

                LEFT JOIN   ploopi_user u
                ON          f.id_user = u.id

                LEFT JOIN   ploopi_workspace w
                ON          f.id_workspace = w.id

                LEFT JOIN   ploopi_mimetype e
                ON          e.ext = f.extension

                WHERE       {$strWhere}
                ";

        $db->query($sql);

        $columns = array();
        $values = array();
        $c = 0;

        $columns['left']['pert'] = array(
            'label' => '',
            'width' => 65,
            'options' => array('sort' => true)
        );

        $columns['auto']['nom'] = array(
            'label' => 'Nom',
            'options' => array('sort' => true)
        );

        if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaysize'])
            $columns['right']['taille'] = array(
                'label' => 'Taille',
                'width' => 90,
                'options' => array('sort' => true)
            );

        $columns['right']['dossier'] = array(
            'label' => 'Dossier',
            'width' => 120,
            'options' => array('sort' => true)
        );

        if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayworkspace'])
            $columns['right']['espace'] = array(
                'label' => 'Espace',
                'width' => 130,
                'options' => array('sort' => true)
            );

        if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayuser'])
            $columns['right']['propriétaire'] = array(
                'label' => 'Propriétaire',
                'width' => 120,
                'options' => array('sort' => true)
            );

        if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaydatetime'])
            $columns['right']['date'] = array(
                'label' => 'Date/Heure',
                'width' => 115,
                'options' => array('sort' => true)
            );

        $columns['actions_right']['actions'] = array(
            'label' => 'Actions',
            'width' => 90
        );


        $booUnoconv = ploopi_getsessionvar('unoconv') === true;
        $booJodconv = ploopi_getsessionvar('jodconv') === true;
        $booConv = $booUnoconv || $booJodconv;

        $columns['actions_right']['actions'] = array('label' => 'Actions', 'width' => $booConv ? '110' : '90');

        if ($db->numrows())
        {
            ?>
            <div style="padding:4px;font-weight:bold;background-color:#f0f0f0;border-bottom:1px solid #c0c0c0;"><?php echo $db->numrows(); ?> fichier(s) trouvé(s)</div>
            <?php
        }
        else
        {
            ?>
            <div style="padding:4px;font-weight:bold;background-color:#f0f0f0;border-bottom:1px solid #c0c0c0;">Aucun fichier trouvé</div>
            <?php
        }



        // DISPLAY FILES
        while ($row = $db->fetchrow())
        {
            if ($row['id_folder'] == 0) $row['fd_name'] = 'Racine';

            $ksize = sprintf("%.02f",$row['size']/1024);
            $ldate = ploopi_timestamp2local($row['timestp_modify']);

            $ico = (file_exists("./img/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

            $icofolder = 'ico_folder';
            if ($row['foldertype'] == 'shared') $icofolder .= '_shared';
            if ($row['foldertype'] == 'public') $icofolder .= '_public';
            if ($row['folder_readonly']) $icofolder .= '_locked';

            $tools = '';

            if (!doc_file_isreadonly($row, _DOC_ACTION_DELETEFILE))
            {
                $tools = '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Êtes vous certain de vouloir supprimer ce fichier ?\')) document.location.href=\''.ploopi_urlencode("admin.php?ploopi_op=doc_filedelete&currentfolder={$row['id_folder']}&docfile_md5id={$row['md5id']}").'\';"><img src="./modules/doc/img/ico_trash.png" /></a>';
            }
            else
            {
                $tools = '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:alert(\'Vous ne disposez pas des autorisations nécessaires pour supprimer ce fichier\');"><img src="./modules/doc/img/ico_trash_grey.png" /></a>';
            }

            $tools .= '
                <a title="Modifier" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$row['id_folder']}&docfile_md5id={$row['md5id']}&docfile_tab=modify").'"><img src="./modules/doc/img/ico_main.png" /></a>
                <a title="Télécharger" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?ploopi_op=doc_filedownload&docfile_md5id={$row['md5id']}").'"><img src="./modules/doc/img/ico_download.png" /></a>
                <a title="Télécharger (ZIP)" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?ploopi_op=doc_filedownloadzip&docfile_md5id={$row['md5id']}").'"><img src="./modules/doc/img/ico_download_zip.png" /></a>
            ';

            if ($booConv)
            {
                $arrRenderer = doc_getrenderer($row['extension']);
                if (isset($arrRenderer[1]) && $arrRenderer[1] == 'unoconv') $tools .= '<a title="Ouvrir en PDF" style="display:block;float:right;" href="'.ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$currentfolder}&docfile_md5id={$row['md5id']}&docfile_tab=pdf").'"><img src="./modules/doc/img/pdf.png" /></a>';
            }

                $blue = 128;

            // Résultats direct sans passer par l'index
            if (!isset($arrRelevance[$row['md5id']]))
            {
                $arrRelevance[$row['md5id']]['relevance'] = 100;
            }

            if ($arrRelevance[$row['md5id']]['relevance']>=50)
            {
                $red = 255-($blue*($arrRelevance[$row['md5id']]['relevance']-50))/50;
                $green = 255;
            }
            else
            {
                $red = 255;
                $green = (255-$blue)+($blue*$arrRelevance[$row['md5id']]['relevance'])/50;
            }


            $color = sprintf("%02X%02X%02X",$red,$green,$blue);


            $values[$c]['values']['pert'] = array(
                'label' => sprintf("<span style=\"width:12px;height:12px;float:left;border:1px solid #a0a0a0;background-color:#%s;margin-right:3px;\"></span>%d %%", $color, $arrRelevance[$row['md5id']]['relevance']),
                'sort_label' => sprintf("%06d", round($arrRelevance[$row['md5id']]['relevance'],2)*100)
            );

            $values[$c]['values']['nom'] = array(
                'label' => "<img src=\"./img/mimetypes/{$ico}\" /><span>&nbsp;".ploopi_htmlentities($row['name'])."</span>",
                'sort_label' => strtolower($row['name'])
            );

            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaysize'])
                $values[$c]['values']['taille'] = array(
                    'label' => "{$ksize} ko",
                    'style' => 'text-align:right',
                    'sort_label' => sprintf("%016d", $ksize*100)
                );

            $values[$c]['values']['dossier'] = array(
                'label' =>'<img style="float:left;" src="./modules/doc/img/'.$icofolder.'.png" /><span style="display:block;margin-left:20px;">'.ploopi_htmlentities($row['fd_name']).'</span>',
                'sort_label' => strtolower($row['fd_name'])
            );

            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayuser'])
                $values[$c]['values']['propriétaire'] = array(
                    'label' => empty($row['user_id']) ? '<em>supprimé</em>' : ploopi_htmlentities($row['login'])
                );

            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displayworkspace'])
                $values[$c]['values']['espace'] = array(
                    'label' => ploopi_htmlentities($row['label'])
                );

            if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_explorer_displaydatetime'])
                $values[$c]['values']['date'] = array(
                    'label' => ploopi_htmlentities($ldate['date'].' '.substr($ldate['time'],0,5)),
                    'sort_label' => $row['timestp_modify']
                );

            $values[$c]['values']['actions'] = array(
                'label' => $tools
            );

            $values[$c]['description'] = ploopi_htmlentities($row['description']);
            $values[$c]['link'] = ploopi_urlencode("admin.php?op=doc_fileform&currentfolder={$row['id_folder']}&docfile_md5id={$row['md5id']}&docfile_tab=open");
            $values[$c]['style'] = '';

            $c++;
        }

        $skin->display_array($columns, $values, 'docsearch', array('sortable' => true, 'orderby_default' => 'pert', 'sort_default' => 'DESC', 'limit' => 100));
    }
}
?>
