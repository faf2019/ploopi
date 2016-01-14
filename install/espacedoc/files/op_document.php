<?php
/**
 * Opérations sur les documents
 *
 * @package espacedoc
 * @subpackage op
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Switch sur les différentes opérations possibles
 */

switch($_REQUEST['ploopi_op'])
{

    case 'espacedoc_document_telecharger':
        ploopi_init_module('espacedoc', false, false, false);

        include_once './modules/espacedoc/classes/class_espacedoc_document.php';

        $objDocument = new espacedoc_document();

        if (!empty($_GET['espacedoc_document_id']) && is_numeric($_GET['espacedoc_document_id']) && $objDocument->open($_GET['espacedoc_document_id']))
        {
            $filepath = $objDocument->getfilepath();
            if (file_exists($filepath)) ploopi_downloadfile($filepath, $objDocument->fields['fichier']);
        }
        ploopi_die();
    break;

    case 'espacedoc_document_enregistrer':
        ploopi_init_module('espacedoc', false, false, false);

        include_once './modules/espacedoc/classes/class_espacedoc_document.php';

        $objDocument = new espacedoc_document();

        if (!empty($_GET['espacedoc_document_id'])) $objDocument->open($_GET['espacedoc_document_id']);

        $objDocument->setvalues($_POST, 'espacedoc_document_');
        $objDocument->save();

        // en mode CGI, il faut récupérer les infos des fichiers uploadés (via le fichier lock)
        // cf class Cupload
        // on écrit tout dans $_FILES pour retomber sur nos pieds dans la suite des traitements
        if (_PLOOPI_USE_CGIUPLOAD && !empty($_POST['sid']))
        {
            if (!empty($_GET['error']) && $_GET['error'] == 'notwritable')
            {
                ploopi_die($_GET['error']);
            }

            define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP.'/');
            include './lib/cupload/Cupload.class.php';

            $_sId = $_POST['sid'];
            $uploader = new CUploadSentinel;
            $uploader->__init($_sId);

            if (!empty($uploader->files))
            {
                foreach($uploader->files as $key => $file)
                {
                    $_FILES[$file['name']] = array(
                        'name'      =>  $file['filename'],
                        'type'      =>  $file['mime'],
                        'tmp_name'  =>  UPLOAD_PATH.$file['tmpname'],
                        'error'     =>  0,
                        'size'      =>  $file['size']
                    );
                }
            }

            $uploader->clear();
        }

        if (!empty($_FILES['_espacedoc_document_fichier']['name']) && rename($_FILES['_espacedoc_document_fichier']['tmp_name'], $objDocument->getfilepath()))
        {
            $objDocument->fields['fichier'] =  $_FILES['_espacedoc_document_fichier']['name'];
            $objDocument->save(true);
        }

        ploopi_redirect('admin.php?espacedoc_saved');
    break;


    case 'espacedoc_document_supprimer':
        ploopi_init_module('espacedoc', false, false, false);

        if (!empty($_GET['espacedoc_element_list']))
        {
            include_once './modules/espacedoc/classes/class_espacedoc_document.php';

            $element_array = explode(',', $_GET['espacedoc_element_list']);
            foreach($element_array as $elementid)
            {
                $objDocument = new espacedoc_document();
                if ($objDocument->open($elementid) && ($objDocument->fields['id_user'] == $_SESSION['ploopi']['userid'] || ploopi_isactionallowed(_ESPACEDOC_ACTION_ADMIN))) $objDocument->delete();
            }
        }

        ploopi_redirect('admin.php');
    break;

    case 'espacedoc_document_modifier':
        ploopi_init_module('espacedoc', false, false, false);

        ob_start();
        include_once './modules/espacedoc/classes/class_espacedoc_document.php';

        $objDocument = new espacedoc_document();

        if (!empty($_GET['espacedoc_element_id']) && is_numeric($_GET['espacedoc_element_id']) && $objDocument->open($_GET['espacedoc_element_id']))
        {
            $objUser = new user();
            $objWorkspace = new workspace();

            $strUserName = ($objUser->open($objDocument->fields['id_user'])) ? "{$objUser->fields['lastname']} {$objUser->fields['firstname']}" : '<em>Utilisateur supprimé</em>';
            $strWorkspace = ($objWorkspace->open($objDocument->fields['id_workspace'])) ? $objWorkspace->fields['label'] : '<em>Espace supprimé</em>';

            $arrDate = ploopi_timestamp2local($objDocument->fields['timestp_create']);

            $sid = espacedoc_guid();
            $max_filesize = espacedoc_max_filesize();

            ?>

            <div class="espacedoc_titre">
            <h1>Modification d'un document</h1>
            </div>

            <form action="<? echo ploopi_urlencode("admin.php?ploopi_op=espacedoc_document_enregistrer&espacedoc_document_id={$objDocument->fields['id']}"); ?>" method="post" enctype="multipart/form-data" onsubmit="javascript:return espacedoc_document_valider_modif(this);">

            <div style="padding:6px;margin:auto;background:#f0f0f0;">
                <p class="espacedoc_va">
                    <label>Créé par :</label>
                    <span><? echo $strUserName; ?> (<? echo $strWorkspace; ?>)</span>

                    <span style="margin-left:10px;"></span>

                    <label>Date de mise en ligne :</label>
                    <span>le <? echo $arrDate['date']; ?> à <? echo $arrDate['time']; ?></span>
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
                        <option value="<? echo $id_th; ?>" <? if ($id_th == $objDocument->fields['id_theme']) echo 'selected="selected"'; ?>><? echo ploopi_htmlentities($th['libelle']); ?></option>
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
                    <input type="text" class="text" id="espacedoc_document_intitule" name="espacedoc_document_intitule" value="<? echo ploopi_htmlentities($objDocument->fields['intitule']); ?>" tabindex="110" style="width:500px;" />
                </p>

                <p class="espacedoc_va">
                    <label>Télécharger le Fichier :</label>
                    <a style="font-weight:bold;" href="<? echo ploopi_urlencode("admin.php?ploopi_op=espacedoc_document_telecharger&espacedoc_document_id={$objDocument->fields['id']}"); ?>" tabindex="120"><? echo ploopi_htmlentities($objDocument->fields['fichier']); ?></a>
                </p>

                <p class="espacedoc_va">
                    <label>Nouveau fichier à déposer :</label>
                    <input type="file" class="text" id="_espacedoc_document_fichier" name="_espacedoc_document_fichier" tabindex="120" />
                    <span> (Taille maxi autorisée : <? echo sprintf("%.01f", $max_filesize/1024); ?> Mo</span>)
                </p>

                <div class="percentImage1" id="espacedoc_progressbar" style="display:none;">[ Loading Progress Bar ]</div>
                <div id="espacedoc_progressbar_txt"></div>

                <div style="clear:both;text-align:right;padding:4px;">
                    <em>* champ obligatoire&nbsp;&nbsp;</em>
                    <input type="button" class="button" value="Abandonner" onclick="javascript:ploopi_hidepopup('popup_document');">
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
                espacedoc_sstheme_refresh(
                    'espacedoc_document_id_sstheme',
                    '<? echo addslashes($objDocument->fields['id_theme']); ?>',
                    '<? echo addslashes($objDocument->fields['id_sstheme']); ?>'
                );

                espacedoc_progressbar = new JS_BRAMUS.jsProgressBar($('espacedoc_progressbar'), 0, {animate: true, showText: false, width: 240, height: 10});
            </script>
            <?
            $content = ob_get_contents();
            ob_end_clean();

            include_once './modules/espacedoc/include/global.php';

            echo $skin->create_popup("Modification d'un document", $content, 'popup_document');
        }
        ploopi_die();
    break;

    case 'espacedoc_document_consulter':
        ploopi_init_module('espacedoc', false, false, false);

        ob_start();
        include_once './modules/espacedoc/classes/class_espacedoc_document.php';
        include_once './modules/espacedoc/classes/class_espacedoc_theme.php';
        include_once './modules/espacedoc/classes/class_espacedoc_sstheme.php';

        $objDocument = new espacedoc_document();

        if (!empty($_GET['espacedoc_element_id']) && is_numeric($_GET['espacedoc_element_id']) && $objDocument->open($_GET['espacedoc_element_id']))
        {
            $objUser = new user();
            $objWorkspace = new workspace();
            $objTheme = new espacedoc_theme();
            $objSsTheme = new espacedoc_sstheme();

            $strUserName = ($objUser->open($objDocument->fields['id_user'])) ? "{$objUser->fields['lastname']} {$objUser->fields['firstname']}" : '<em>Utilisateur supprimé</em>';
            $strWorkspace = ($objWorkspace->open($objDocument->fields['id_workspace'])) ? $objWorkspace->fields['label'] : '<em>Espace supprimé</em>';

            $strTheme = ($objTheme->open($objDocument->fields['id_theme'])) ? $objTheme->fields['libelle'] : '';
            $strSsTheme = ($objSsTheme->open($objDocument->fields['id_sstheme'])) ? $objSsTheme->fields['libelle'] : '';

            $arrDate = ploopi_timestamp2local($objDocument->fields['timestp_create']);
            ?>

            <div class="espacedoc_titre">
            <h1>Consultation d'un document</h1>
            </div>

            <form action="<? echo ploopi_urlencode("admin.php?ploopi_op=espacedoc_document_enregistrer&espacedoc_document_id={$objDocument->fields['id']}"); ?>" method="post" enctype="multipart/form-data" onsubmit="javascript:return espacedoc_document_valider_modif(this);">
            <div style="padding:6px;margin:auto;background:#f0f0f0;">
                <p class="espacedoc_va">
                    <label>Créé par :</label>
                    <span><? echo $strUserName; ?> (<? echo $strWorkspace; ?>)</span>

                    <span style="margin-left:10px;"></span>

                    <label>Date de mise en ligne :</label>
                    <span>le <? echo $arrDate['date']; ?> à <? echo $arrDate['time']; ?></span>
                </p>

                <p class="espacedoc_va">
                    <label><? echo ploopi_getparam('espacedoc_theme'); ?> :</label>
                    <span><? echo $strTheme; ?></span>

                    <span style="margin-left:10px;"></span>

                    <label><? echo ploopi_getparam('espacedoc_sstheme'); ?> :</label>
                    <span><? echo $strSsTheme; ?></span>
                </p>

                <p class="espacedoc_va">
                    <label>Intitulé du document :</label>
                    <span><? echo ploopi_htmlentities($objDocument->fields['intitule']); ?></span>
                </p>

                <p class="espacedoc_va">
                    <label>Télécharger le Fichier :</label>
                    <a style="font-weight:bold;" href="<? echo ploopi_urlencode("admin.php?ploopi_op=espacedoc_document_telecharger&espacedoc_document_id={$objDocument->fields['id']}"); ?>" tabindex="120"><? echo ploopi_htmlentities($objDocument->fields['fichier']); ?></a>
                </p>

                <div style="clear:both;text-align:right;padding:4px;">
                    <input type="button" class="button" value="Fermer cette fenêtre" onclick="javascript:ploopi_hidepopup('popup_document');">
                </div>
            </div>
            </form>
            <?
            $content = ob_get_contents();
            ob_end_clean();

            include_once './modules/espacedoc/include/global.php';

            echo $skin->create_popup("Consultation d'un document", $content, 'popup_document');
        }
        ploopi_die();
    break;

}


?>
