<?php
/**
 * Opérations sur les thèmes
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
    case 'espacedoc_theme_supprimer':
        if (!empty($_GET['espacedoc_element_list']))
        {
            include_once './modules/espacedoc/classes/class_espacedoc_theme.php';

            $element_array = explode(',', $_GET['espacedoc_element_list']);
            foreach($element_array as $elementid)
            {
                $objTheme = new espacedoc_theme();
                if ($objTheme->open($elementid)) $objTheme->delete();
            }
        }
        ploopi_redirect('admin.php');
    break;

    case 'espacedoc_theme_enregistrer':
        include_once './modules/espacedoc/classes/class_espacedoc_theme.php';
        $theme = new espacedoc_theme();
        if (!empty($_POST['espacedoc_theme_id']) && is_numeric($_POST['espacedoc_theme_id'])) $theme->open($_POST['espacedoc_theme_id']);
        $theme->setvalues($_POST, 'espacedoc_theme_');

        if (!isset($_POST['espacedoc_theme_actif'])) $theme->fields['actif'] = 0;

        $theme->save();
        ?>
        <script type="text/javascript">
            window.parent.document.location.reload();
        </script>
        <?
        ploopi_die();
    break;

    case 'espacedoc_theme_ajouter':
    case 'espacedoc_theme_modifier':
        ob_start();
        include_once './modules/espacedoc/classes/class_espacedoc_theme.php';

        $theme = new espacedoc_theme();

        switch($_REQUEST['ploopi_op'])
        {
            case 'espacedoc_theme_ajouter':
                $theme->init_description();
            break;

            case 'espacedoc_theme_modifier':
                if (!empty($_GET['espacedoc_element_id']) && is_numeric($_GET['espacedoc_element_id'])) $theme->open($_GET['espacedoc_element_id']);
                else $theme->init_description();
            break;
        }
        ?>
        <form method="post" onsubmit="javascript:return espacedoc_theme_valider(this);" target="espacedoc_theme_ajouter">
        <input type="hidden" name="ploopi_op" value="espacedoc_theme_enregistrer">
        <input type="hidden" name="espacedoc_theme_id" value="<? echo $theme->fields['id']; ?>">
        <div class=ploopi_form>
            <p>
                <label>Libellé:</label>
                <input name="espacedoc_theme_libelle" type="text" class="text" value="<? echo ploopi_htmlentities($theme->fields['libelle']); ?>">
            </p>
            <p onclick="javascript:ploopi_checkbox_click(event,'espacedoc_theme_actif');">
                <label for="espacedoc_theme_actif">Actif:</label>
                <input name="espacedoc_theme_actif" id="espacedoc_theme_actif" type="checkbox" class="checkbox" value="1" <? if ($theme->fields['actif']) echo 'checked'; ?> tabindex="111" />
            </p>
        </div>
        <div style="padding:4px;text-align:right;">
            <input type="reset" class="button" value="Réinitialiser" />
            <input type="submit" class="button" value="Enregistrer" />
        </div>
        </form>
        <iframe class="espacedoc_iframe" name="espacedoc_theme_ajouter" src="./img/blank.gif"></iframe>
        <?
        $content = ob_get_contents();
        ob_end_clean();

        include_once './modules/espacedoc/include/global.php';

        $titre = ($_REQUEST['ploopi_op'] == 'espacedoc_theme_ajouter') ? 'Ajout' : 'Modification';

        echo $skin->create_popup("{$titre} d'un élément", $content, 'popup_theme');
        ploopi_die();
    break;
}
?>
