<?php
/*
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
 * Genère une instance de FCK Editor
 *
 * @param string $strInstanceName nom de l'instance
 * @param string $strContent contenu html par défaut
 * @param string $strWidth largeur de l'éditeur (px ou %)
 * @param string $strHeight hauteur de l'éditeur (px ou %)
 * @param array $arrConfig paramètres de configuration
 * @param array $arrProperties propriétés
 */

function ploopi_fckeditor($strInstanceName, $strContent, $strWidth, $strHeight, $arrConfig = null, $arrProperties = null)
{
    //include_once './FCKeditor/fckeditor.php';
    include_once './lib/fckeditor/fckeditor.php';

    $oFCKeditor = new FCKeditor($strInstanceName);

    //$oFCKeditor->BasePath = './FCKeditor/';
    $oFCKeditor->BasePath = './lib/fckeditor/';

    // default value
    $oFCKeditor->Value = $strContent;

    // width & height
    $oFCKeditor->Width = $strWidth;
    $oFCKeditor->Height = $strHeight;

    $oFCKeditor->Config['BaseHref'] = _PLOOPI_BASEPATH.'/';
    $oFCKeditor->Config['LinkBrowserURL'] = _PLOOPI_BASEPATH.'/admin-light.php?ploopi_op=doc_selectfile';
    $oFCKeditor->Config['ImageBrowserURL'] = _PLOOPI_BASEPATH.'/admin-light.php?ploopi_op=doc_selectimage';
    $oFCKeditor->Config['FlashBrowserURL'] = _PLOOPI_BASEPATH.'/admin-light.php?ploopi_op=doc_selectflash';

    // config
    if (isset($arrConfig)) foreach($arrConfig as $strKey => $strValue) $oFCKeditor->Config[$strKey] = $strValue;

    // properties
    if (isset($arrProperties)) foreach($arrProperties as $strKey => $strValue) $oFCKeditor->$strKey = $strValue;

    // render
    $oFCKeditor->Create('FCKeditor_'.md5(uniqid(rand(), true))) ;
}


function ploopi_ckeditor($strInstanceName, $strContent, $strWidth, $strHeight, $arrConfig = null, $arrProperties = null)
{
    include_once './lib/ckeditor/ckeditor.php';

    $oFCKeditor = new CKEditor($strInstanceName);

    $oFCKeditor->basePath = './lib/ckeditor/';

    // width & height
    $oFCKeditor->config['width'] = $strWidth;
    $oFCKeditor->config['height'] = $strHeight;

    $oFCKeditor->config['baseHref'] = _PLOOPI_BASEPATH.'/';
    $oFCKeditor->config['filebrowserBrowseUrl'] = _PLOOPI_BASEPATH.'/admin-light.php?ploopi_op=doc_selectfile';
    $oFCKeditor->config['filebrowserImageBrowseUrl'] = _PLOOPI_BASEPATH.'/admin-light.php?ploopi_op=doc_selectimage';
    $oFCKeditor->config['filebrowserFlashBrowseUrl'] = _PLOOPI_BASEPATH.'/admin-light.php?ploopi_op=doc_selectflash';

    $oFCKeditor->config['filebrowserUrlBrowseUrl'] = _PLOOPI_BASEPATH.'/admin-light.php?ploopi_op=doc_selectfile';

    $oFCKeditor->config['filebrowserWindowWidth'] = '640';
    $oFCKeditor->config['filebrowserWindowHeight'] = '480';

    $oFCKeditor->config['toolbar'] = array(
        array( 'Source', '-', 'Bold', 'Italic', 'Underline', 'Strike' ),
        array( 'Image', 'Link', 'Unlink', 'Anchor', 'MediaEmbed' )
    );


    $oFCKeditor->config['sharedSpaces'] = array('top' => 'xToolbar');

    $oFCKeditor->config['removePlugins'] = 'maximize,resize';

    $oFCKeditor->config['extraPlugins'] = '';

    // config
    // if (isset($arrConfig)) foreach($arrConfig as $strKey => $strValue) $oFCKeditor->config[$strKey] = $strValue;

    // properties
    // if (isset($arrProperties)) foreach($arrProperties as $strKey => $strValue) $oFCKeditor->$strKey = $strValue;

    // render
    $oFCKeditor->editor('FCKeditor_'.md5(uniqid(rand())), $strContent);
}
