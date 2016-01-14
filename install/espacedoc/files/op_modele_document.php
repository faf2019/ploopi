<?php
/**
 * Opérations sur les modèles de document
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
    case 'espacedoc_modele_document_telecharger':
        include_once './modules/espacedoc/classes/class_espacedoc_modele_document.php';
        $modele_document = new espacedoc_modele_document();
        if (!empty($_GET['espacedoc_modele_document_id']) && is_numeric($_GET['espacedoc_modele_document_id']) && $modele_document->open($_GET['espacedoc_modele_document_id']))
        {
            $filepath = _PLOOPI_PATHDATA._PLOOPI_SEP.'espacedoc'._PLOOPI_SEP.'modeles'._PLOOPI_SEP.$modele_document->fields['fichier'];
            if (file_exists($filepath)) ploopi_downloadfile($filepath, $modele_document->fields['fichier']);
        }
        ploopi_die();
    break;

    case 'espacedoc_modele_document_supprimer':
        if (!empty($_GET['espacedoc_element_list']))
        {
            include_once './modules/espacedoc/classes/class_espacedoc_modele_document.php';

            $element_array = explode(',', $_GET['espacedoc_element_list']);
            foreach($element_array as $elementid)
            {
                $modele_document = new espacedoc_modele_document();
                $modele_document->open($elementid);
                $modele_document->delete();
            }
        }
        ploopi_redirect('admin.php');
        ploopi_die();
    break;

    case 'espacedoc_modele_document_enregistrer':
        include_once './modules/espacedoc/classes/class_espacedoc_modele_document.php';
        $modele_document = new espacedoc_modele_document();
        if (!empty($_POST['espacedoc_modele_document_id']) && is_numeric($_POST['espacedoc_modele_document_id'])) $modele_document->open($_POST['espacedoc_modele_document_id']);
        $modele_document->setvalues($_POST, 'espacedoc_modele_document_');


        if (!empty($_FILES['_espacedoc_modele_document_fichier']['name']))
        {
            $filepath = _PLOOPI_PATHDATA._PLOOPI_SEP.'espacedoc'._PLOOPI_SEP.'modeles';
            ploopi_makedir($filepath);

            if (move_uploaded_file($_FILES['_espacedoc_modele_document_fichier']['tmp_name'], $filepath._PLOOPI_SEP.$_FILES['_espacedoc_modele_document_fichier']['name']))
            {
                $modele_document->fields['fichier'] = $_FILES['_espacedoc_modele_document_fichier']['name'];
            }
            else
            {
                $modele_document->fields['fichier'] = '';
            }
        }

        $modele_document->save();
        ?>
        <script type="text/javascript">
            window.parent.document.location.reload();
        </script>
        <?
        ploopi_die();
    break;

    case 'espacedoc_modele_document_ajouter':
    case 'espacedoc_modele_document_modifier':
        ob_start();
        ploopi_init_module('espacedoc', false, false, false);
        include_once './modules/espacedoc/classes/class_espacedoc_modele_document.php';

        $modele_document = new espacedoc_modele_document();

        switch($_REQUEST['ploopi_op'])
        {
            case 'espacedoc_modele_document_ajouter':
                $modele_document->init_description();
            break;

            case 'espacedoc_modele_document_modifier':
                if (!empty($_GET['espacedoc_element_id']) && is_numeric($_GET['espacedoc_element_id'])) $modele_document->open($_GET['espacedoc_element_id']);
                else $modele_document->init_description();
            break;
        }
        ?>
        <form method="post" onsubmit="javascript:return espacedoc_modele_document_valider(this);" target="espacedoc_modele_document_ajouter" enctype="multipart/form-data">
        <input type="hidden" name="ploopi_op" value="espacedoc_modele_document_enregistrer">
        <input type="hidden" name="espacedoc_modele_document_id" value="<? echo $modele_document->fields['id']; ?>">
        <div class=ploopi_form>
            <p>
                <label>Ref/Type:</label>
                <input name="espacedoc_modele_document_type" type="text" class="text" value="<? echo ploopi_htmlentities($modele_document->fields['type']); ?>" maxlength="10" style="width:100px;" tabindex="101" />
            </p>
            <p>
                <label>Intitulé:</label>
                <input name="espacedoc_modele_document_libelle" type="text" class="text" value="<? echo ploopi_htmlentities($modele_document->fields['libelle']); ?>" tabindex="102" />
            </p>
            <p>
                <label>Fichier:</label>
                <input name="_espacedoc_modele_document_fichier" type="file" class="text" tabindex="103" />
            </p>
            <?
            if ($_REQUEST['ploopi_op'] == 'espacedoc_modele_document_modifier')
            {
                ?>
                <p>
                    <label>Fichier actuel:</label>
                    <span><a href="<? echo ploopi_urlencode('admin.php?ploopi_op=espacedoc_modele_document_telecharger&espacedoc_modele_document_id='.$modele_document->fields['id']); ?>"><? echo ploopi_htmlentities($modele_document->fields['fichier']); ?></a> (cliquez sur le lien pour télécharger le document)</span>
                </p>
                <?
            }
            ?>
        </div>
        <div style="padding:4px;text-align:right;">
            <input type="reset" class="button" value="Réinitialiser">
            <input type="submit" class="button" value="Enregistrer">
        </div>
        </form>
        <iframe class="espacedoc_iframe" name="espacedoc_modele_document_ajouter" src="./img/blank.gif"></iframe>
        <?
        $content = ob_get_contents();
        ob_end_clean();

        $titre = ($_REQUEST['ploopi_op'] == 'espacedoc_modele_document_ajouter') ? 'Ajout' : 'Modification';

        echo $skin->create_popup("{$titre} d'un modèle de document", $content, 'popup_modele_document');
        ploopi_die();
    break;
}
?>
