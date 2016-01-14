<?php
/**
 * Opérations sur les départements
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
    case 'espacedoc_departement_supprimer':
        if (!empty($_GET['espacedoc_element_list']))
        {
            include_once './modules/espacedoc/classes/class_espacedoc_departement.php';

            $element_array = explode(',', $_GET['espacedoc_element_list']);
            foreach($element_array as $elementid)
            {
                $objTheme = new espacedoc_departement();
                if ($objTheme->open($elementid)) $objTheme->delete();
            }
        }
        ploopi_redirect('admin.php');
    break;

    case 'espacedoc_departement_enregistrer':
        include_once './modules/espacedoc/classes/class_espacedoc_departement.php';
        $departement = new espacedoc_departement();
        if (!empty($_POST['espacedoc_departement_id']) && is_numeric($_POST['espacedoc_departement_id'])) $departement->open($_POST['espacedoc_departement_id']);
        $departement->setvalues($_POST, 'espacedoc_departement_');

        if (!isset($_POST['espacedoc_departement_actif'])) $departement->fields['actif'] = 0;

        $departement->save();
        ?>
        <script type="text/javascript">
            window.parent.document.location.reload();
        </script>
        <?
        ploopi_die();
    break;

    case 'espacedoc_departement_ajouter':
    case 'espacedoc_departement_modifier':
        ob_start();
        include_once './modules/espacedoc/classes/class_espacedoc_departement.php';

        $departement = new espacedoc_departement();

        switch($_REQUEST['ploopi_op'])
        {
            case 'espacedoc_departement_ajouter':
                $departement->init_description();
            break;

            case 'espacedoc_departement_modifier':
                if (!empty($_GET['espacedoc_element_id']) && is_numeric($_GET['espacedoc_element_id'])) $departement->open($_GET['espacedoc_element_id']);
                else $departement->init_description();
            break;
        }
        ?>
        <form method="post" onsubmit="javascript:return espacedoc_departement_valider(this);" target="espacedoc_departement_ajouter">
        <input type="hidden" name="ploopi_op" value="espacedoc_departement_enregistrer">
        <input type="hidden" name="espacedoc_departement_id" value="<? echo $departement->fields['id']; ?>">
        <div class=ploopi_form>
            <p>
                <label>Libellé:</label>
                <input name="espacedoc_departement_libelle" type="text" class="text" value="<? echo ploopi_htmlentities($departement->fields['libelle']); ?>">
            </p>
            <p onclick="javascript:ploopi_checkbox_click(event,'espacedoc_departement_actif');">
                <label for="espacedoc_departement_actif">Actif:</label>
                <input name="espacedoc_departement_actif" id="espacedoc_departement_actif" type="checkbox" class="checkbox" value="1" <? if ($departement->fields['actif']) echo 'checked'; ?> tabindex="111" />
            </p>
        </div>
        <div style="padding:4px;text-align:right;">
            <input type="reset" class="button" value="Réinitialiser" />
            <input type="submit" class="button" value="Enregistrer" />
        </div>
        </form>
        <iframe class="espacedoc_iframe" name="espacedoc_departement_ajouter" src="./img/blank.gif"></iframe>
        <?
        $content = ob_get_contents();
        ob_end_clean();

        include_once './modules/espacedoc/include/global.php';

        $titre = ($_REQUEST['ploopi_op'] == 'espacedoc_departement_ajouter') ? 'Ajout' : 'Modification';

        echo $skin->create_popup("{$titre} d'un département", $content, 'popup_departement');
        ploopi_die();
    break;
}
?>
