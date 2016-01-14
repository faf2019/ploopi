<?php
/**
 * Public / Ajout de document(s)
 *
 * @package espacedoc
 * @subpackage public
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

global $arrTheme;

$sid = espacedoc_guid();
$max_filesize = espacedoc_max_filesize();
?>
<div class="espacedoc_titre">
<h1>Mise en ligne d'un document</h1>
</div>

<form action="<? echo ploopi_urlencode('admin.php?ploopi_op=espacedoc_document_enregistrer'); ?>" method="post" enctype="multipart/form-data" onsubmit="javascript:return espacedoc_document_valider(this);">
<input type="hidden" name="MAX_FILE_SIZE" value="<? echo $max_filesize*1024; ?>">
<div style="width:800px;margin:auto;border-width:0px 1px;border-style:solid;border-color:#a0a0a0;background:#f0f0f0;padding:6px;">
    <p class="espacedoc_va">
        <label>Créé par :</label>
        <span><? echo $_SESSION['ploopi']['user']['lastname']; ?> <? echo $_SESSION['ploopi']['user']['firstname']; ?> (<? echo $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label']; ?>)</span>
        <span style="margin-left:10px;"></span>
    </p>

    <p class="espacedoc_va">
        <em>*</em>
        <label><? echo ploopi_getparam('espacedoc_theme'); ?> :</label>
        <select class="select" id="espacedoc_document_id_theme" name="espacedoc_document_id_theme" tabindex="101" style="width:280px;" onchange="javascript:espacedoc_sstheme_refresh('espacedoc_document_id_sstheme', this.value);">
        <option value="" style="font-style:italic;">(Choisir un élément)</option>
        <?
        foreach($arrTheme as $id_th => $th)
        {
            ?>
            <option value="<? echo $id_th; ?>"><? echo ploopi_htmlentities($th['libelle']); ?></option>
            <?
        }
        ?>
        </select>

        <span style="margin-left:10px;"></span>
        <em>*</em>
        <label><? echo ploopi_getparam('espacedoc_sstheme'); ?> :</label>
        <select class="select" id="espacedoc_document_id_sstheme" name="espacedoc_document_id_sstheme" tabindex="102" style="width:280px;">
            <option value="" style="font-style:italic;">(Choisir un élément)</option>
        </select>
    </p>

    <p class="espacedoc_va">
        <em>*</em>
        <label>Intitulé du document :</label>
        <input type="text" class="text" id="espacedoc_document_intitule" name="espacedoc_document_intitule" tabindex="110" style="width:500px;" />
    </p>

    <p class="espacedoc_va">
        <em>*</em>
        <label>Fichier à déposer :</label>
        <input type="file" class="text" id="_espacedoc_document_fichier" name="_espacedoc_document_fichier" tabindex="120" />
        <span> (Taille maxi autorisée : <? echo sprintf("%.01f", $max_filesize/1024); ?> Mo</span>)
    </p>

    <div class="percentImage1" id="espacedoc_progressbar" style="display:none;">[ Loading Progress Bar ]</div>
    <div id="espacedoc_progressbar_txt"></div>

    <div style="clear:both;text-align:right;padding:4px;">
        <em>* champ obligatoire&nbsp;&nbsp;</em>
        <input type="button" class="button" value="Abandonner" onclick="document.location.href='<? echo ploopi_urlencode("admin.php"); ?>';">
        <input type="reset" class="button" value="Réinitialiser">
        <input type="submit" class="button" value="Enregistrer">
    </div>
</div>
</form>

<div id="espacedoc_saved" style="display:none;">
    <div style="padding:20px 4px;text-align:center;color:#00a600;font-weight:bold;border:2px solid #143477;">
    document enregistré
    </div>
</div>

<script type="text/javascript">
var espacedoc_progressbar;

ploopi_window_onload_stock(
    function()
    {
        espacedoc_progressbar = new JS_BRAMUS.jsProgressBar($('espacedoc_progressbar'), 0, {animate: true, showText: false, width: 240, height: 10});
        <?
        if (isset($_REQUEST['espacedoc_saved']))
        {
            ?>
            ploopi_showpopup($('espacedoc_saved').innerHTML, 250, null, true, 'popup_espacedoc_saved', null, 230);
            new PeriodicalExecuter( function(pe) {
                   ploopi_hidepopup('popup_espacedoc_saved');
                   pe.stop();
                }
                ,2
            );
            <?
        }
        ?>

    }
);
</script>
