<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Opérations sur les documents
 *
 * @package ploopi
 * @subpackage filexplorer
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

switch($ploopi_op)
{

    case 'filexplorer_browser':
        if (empty($_GET['filexplorer_id']) || empty($_SESSION['filexplorer'][$_GET['filexplorer_id']])) ploopi_die();

        ob_start();

        require_once './include/classes/cipher.php';
        $cipher = new ploopi_cipher();

        //ploopi_print_r($_GET);

        // Dossier actuel (chaine)
        $strCurrentFolder = empty($_GET['filexplorer_folder']) ? '' : $cipher->decrypt($_GET['filexplorer_folder']);

        // Dossier actuel (tableau)
        $arrCurrentFolder = explode(_PLOOPI_SEP, $strCurrentFolder);

        // Chemin complet actuel (incluant basepath)
        $strCurrentPath = $_SESSION['filexplorer'][$_GET['filexplorer_id']]['basepath']._PLOOPI_SEP.$strCurrentFolder;

        ?>
        <div id="filexplorer">
            <div class="documents_browser">
                <div class="documents_path">
                    <a title="Aller au Dossier Racine" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_filexplorer_browser('<?php echo ploopi_htmlentities($_GET['filexplorer_id']); ?>', '<?php $cipher->crypt(''); ?>');"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_home.png"></a>
                    <div>Emplacement :</div>
                    <?php
                    $strShortCutPath = '';
                    foreach($arrCurrentFolder as $strFolderName)
                    {
                        if ($strFolderName != '') $strShortCutPath .= _PLOOPI_SEP.$strFolderName;
                        ?>
                        <a href="javascript:void(0);" onclick="javascript:ploopi_filexplorer_browser('<?php echo ploopi_htmlentities($_GET['filexplorer_id']); ?>', '<?php echo $cipher->crypt($strShortCutPath); ?>');">
                            <p class="ploopi_va">
                                <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_folder.png" />
                                <span><?php echo ploopi_htmlentities($strFolderName ? $strFolderName : 'Racine'); ?></span>
                            </p>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <?php

                $documents_columns = array();

                $documents_columns['auto']['name'] =
                    array(
                        'label' => 'Nom',
                        'options' => array('sort' => true)
                    );

                if (empty($_SESSION['documents']['fields']) || in_array('type', $_SESSION['documents']['fields']))
                {
                    $documents_columns['right']['type'] =
                        array(
                            'label' => 'Type',
                            'width' => empty($_SESSION['documents']['fields_size']['type']) ? 65 : $_SESSION['documents']['fields_size']['type'],
                            'options' => array('sort' => true)
                        );
                }

                if (empty($_SESSION['documents']['fields']) || in_array('timestp_file', $_SESSION['documents']['fields']))
                {
                    $documents_columns['right']['timestp_file'] =
                        array(
                            'label' => 'Date',
                            'width' => empty($_SESSION['documents']['fields_size']['timestp_file']) ? 80 : $_SESSION['documents']['fields_size']['timestp_file'],
                            'options' => array('sort' => true)
                        );
                }

                if (empty($_SESSION['documents']['fields']) || in_array('size', $_SESSION['documents']['fields']))
                {
                    $documents_columns['right']['size'] =
                        array(
                            'label' => 'Taille',
                            'width' => empty($_SESSION['documents']['fields_size']['size']) ? 90 : $_SESSION['documents']['fields_size']['size'],
                            'options' => array('sort' => true)
                        );
                }

                $documents_values = array();

                clearstatcache();

                $ptFolder = @opendir($strCurrentPath);

                while ($strFileName = @readdir($ptFolder))
                {
                    if ($strFileName != '.' && $strFileName != '..')
                    {
                        //echo '<br />'.$strCurrentPath._PLOOPI_SEP.$strFileName;
                        $strFilePath = $strCurrentPath._PLOOPI_SEP.$strFileName;

                        $boolIsFolder = is_dir($strFilePath);
                        $arrStat = stat($strFilePath);

                       // ploopi_print_r($arrStat);

                        if ($boolIsFolder)
                        {
                            $strFileExtension = '';
                            $strIcon = 'ico_folder.png';
                            $intSortId = 1;

                            // ouverture du sous-dossier pour compter les éléments
                            $intElements = 0;
                            $ptF = @opendir($strFilePath);
                            while ($strFN = @readdir($ptF)) if ($strFN != '.' && $strFN != '..') $intElements++;
                        }
                        else
                        {
                            //echo $strFileExtension = ploopi_file_getextension($strFileName);
                            //$strIcon = "mimetypes/".(file_exists("{$_SESSION['ploopi']['template_path']}/img/documents/mimetypes/ico_{$strFileExtension}.png") ? "ico_{$strFileExtension}.png" : 'ico_default.png');
                            $strIcon = 'mimetypes/ico_default.png';
                            $intSortId = 2;
                            $intElements = 0;
                        }

                        $strDate = ploopi_unixtimestamp2local($arrStat['mtime']);

                        $documents_values[] =
                            array(
                                'values' =>
                                    array(
                                        'name' =>
                                            array(
                                                'label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/documents/{$strIcon}\" /><span>&nbsp;".ploopi_htmlentities($strFileName)."</span>",
                                                'sort_label' => "{$intSortId}_{$strFileName}"
                                            ),
                                        'type' =>
                                            array(
                                                'label' => $boolIsFolder ? 'Dossier' : 'Fichier',
                                                'sort_label' => $intSortId
                                            ),
                                        'timestp_file' =>
                                            array(
                                                'label' => ploopi_htmlentities(substr($strDate,0,10)),
                                                'sort_label' => $arrStat['mtime']
                                            ),
                                        'size' =>
                                            array(
                                                'label' => ploopi_htmlentities($boolIsFolder ? "{$intElements} element(s)" : sprintf("%s Ko", number_format(($arrStat['size']/1024),1,".", " "))),
                                                'sort_label' => $intSortId.'_'.($boolIsFolder ? sprintf("%020d", $intElements) : sprintf("%020d", $arrStat['size'])),
                                                'style' => 'text-align:right;'
                                            )
                                    ),
                                'description' => '',
                                'link' => 'javascript:void(0);',
                                'option' =>
                                    $boolIsFolder
                                    ? 'onclick="javascript:ploopi_filexplorer_browser(\''.$_GET['filexplorer_id'].'\', \''.$cipher->crypt($strCurrentFolder._PLOOPI_SEP.$strFileName).'\');"'
                                    : 'onclick="javascript:$(\''.$_SESSION['filexplorer'][$_GET['filexplorer_id']]['destfield'].'\').value=\''.$strCurrentFolder._PLOOPI_SEP.$strFileName.'\';ploopi_hidepopup(\'ploopi_filexplorer_popup\');"',
                                'style' => ''
                            );
                    }
                }

                closedir($ptFolder);

                $skin->display_array(
                    $documents_columns,
                    $documents_values,
                    'ploopi_documents',
                    array(
                        'sortable' => true,
                        'orderby_default' => 'name',
                        'sort_default' => 'DESC',
                    )
                );
                ?>
            </div>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        echo $skin->create_popup('Explorateur de fichiers', $content, 'ploopi_filexplorer_popup');

        ploopi_die();
    break;

}
