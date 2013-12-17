<?php
/*
    Copyright (c) 2007-2010 Ovensia
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
 * Formulaire d'import CSV
 *
 * @package directory
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Formulaire d'import CSV
 */

?>
<script type="text/javascript">

function filesubmit_onload(iFrameName)
{
    var oIframe = $(iFrameName);
    var oDoc = oIframe.contentWindow || oIframe.contentDocument;
    if (oDoc.document) oDoc = oDoc.document;
    
    $('directory_import_info').innerHTML = oDoc.body.innerHTML; 
}

function filesubmit(form)
{
    // Image chargement
    $('directory_import_info').style.display = 'block';
    ploopi_ajaxloader('directory_import_info');

    // Création iframe dynamique
    var iFrameName = 'f' + Math.floor(Math.random() * 99999);
    var d = document.createElement('DIV');
    d.innerHTML = '<iframe style="display:none;" src="about:blank" id="'+iFrameName+'" name="'+iFrameName+'" onload="javascript:filesubmit_onload(\''+iFrameName+'\');"></iframe>';
    document.body.appendChild(d);
    
    // Redirection du formulaire dans l'iframe
    form.setAttribute('target', iFrameName);

    // Validation formulaire    
    return true;
}
</script>



<div style="border-bottom:2px solid #c0c0c0;padding:4px;overflow:auto;">
    Vous pouvez importer des données dans une rubrique avec un fichier CSV.
    Ce fichier doit utiliser le séparateur "," ou ";".
    <br />
    <br />La première ligne doit être une ligne de description des colonnes fournies.
    <br />Cela signifie qu'elle contient certains ou tous les champs de la liste ci-dessous :
    
    <ul style="overflow:auto;">
    <?
    foreach($arrDirectoryImportFields as $strKey => $strValue)
    {
        ?>
        <li style="float:left;width:50%;"><strong><?php echo ploopi_htmlentities($strKey); ?>:</strong> <span><?php echo ploopi_htmlentities($strValue); ?></span></li>
        <?
    }
    //onsubmit="javascript:$('directory_import_info').style.display='block'; ploopi_xmlhttprequest_submitform($('directory_import_form'), 'directory_import_info'); return false;">
    //onsubmit="javascript:filesubmit('toto');" 
    ?>
    </ul>
    
    <form id="directory_import_form" action="<?php echo ploopi_urlencode("admin.php?ploopi_op=directory_import".(!empty($intHeadingId) ? "&directory_heading_id={$intHeadingId}" : '')); ?>" method="post" onsubmit="javascript:filesubmit(this);" enctype="multipart/form-data"> 
    <div class="ploopi_form" style="clear:both;margin-top:10px;">
        <p>
            <label>Fichier CSV:</label>
            <input type="file" class="text" name="directory_import_file" tabindex="100" />
        </p>
        <p>
            <label>Séparateur:</label>
            <select class="select" name="directory_import_sep" tabindex="102" style="width:40px;">
                <option value="<? echo ploopi_htmlentities(',') ?>"><? echo ploopi_htmlentities(',') ?></option>
                <option value="<? echo ploopi_htmlentities(';') ?>"><? echo ploopi_htmlentities(';') ?></option>
            </select>
        </p>
    </div>
        
    <div style="clear:both;padding:2px 4px;text-align:right;">
        <input type="button" class="button" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php"); ?>';" tabindex="141" />
        <input type="submit" class="button" value="Importer" tabindex="140" />
    </div>
    </form>
    
    <div id="directory_import_info" style="display:none;"></div>
</div>
