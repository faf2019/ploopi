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
    case 'espacedoc_sstheme_supprimer':
        if (!empty($_GET['espacedoc_element_list']))
        {
            include_once './modules/espacedoc/classes/class_espacedoc_sstheme.php';

            $element_array = explode(',', $_GET['espacedoc_element_list']);
            foreach($element_array as $elementid)
            {
                $objSsTheme = new espacedoc_sstheme();
                if ($objSsTheme->open($elementid)) $objSsTheme->delete();
            }
        }
        ploopi_redirect('admin.php');
        ploopi_die();
    break;

    case 'espacedoc_sstheme_enregistrer':
        include_once './modules/espacedoc/classes/class_espacedoc_sstheme.php';
        $sstheme = new espacedoc_sstheme();
        if (!empty($_POST['espacedoc_sstheme_id']) && is_numeric($_POST['espacedoc_sstheme_id'])) $sstheme->open($_POST['espacedoc_sstheme_id']);
        $sstheme->setvalues($_POST, 'espacedoc_sstheme_');

        if (!isset($_POST['espacedoc_sstheme_actif'])) $sstheme->fields['actif'] = 0;

        $sstheme->save();
        ?>
        <script type="text/javascript">
            window.parent.document.location.reload();
        </script>
        <?
        ploopi_die();
    break;

    case 'espacedoc_sstheme_ajouter':
    case 'espacedoc_sstheme_modifier':
        ob_start();
        ploopi_init_module('espacedoc');

        include_once './modules/espacedoc/classes/class_espacedoc_sstheme.php';

        $workspace = new workspace();
        $sstheme = new espacedoc_sstheme();

        $sql =  "
                SELECT      *
                FROM        ploopi_mod_espacedoc_theme
                ORDER BY    libelle
                 ";
        $db->query($sql);

        $arrTheme = $db->getarray();

        switch($_REQUEST['ploopi_op'])
        {
            case 'espacedoc_sstheme_ajouter':
                $sstheme->init_description();
            break;

            case 'espacedoc_sstheme_modifier':
                if (!empty($_GET['espacedoc_element_id']) && is_numeric($_GET['espacedoc_element_id'])) $sstheme->open($_GET['espacedoc_element_id']);
                else $sstheme->init_description();
            break;
        }
        ?>
        <form method="post" onsubmit="javascript:return espacedoc_sstheme_valider(this);" target="espacedoc_sstheme_ajouter">
        <input type="hidden" name="ploopi_op" value="espacedoc_sstheme_enregistrer">
        <input type="hidden" name="espacedoc_sstheme_id" value="<? echo $sstheme->fields['id']; ?>">
        <div class="ploopi_form">
            <p>
                <label><? echo ploopi_getparam('espacedoc_theme'); ?>:</label>
                <select name="espacedoc_sstheme_id_theme" class="select">
                    <option value="">(choisir)</option>
                    <?
                    foreach($arrTheme as $theme)
                    {
                        ?>
                        <option <? if ($sstheme->fields['id_theme'] == $theme['id']) echo 'selected'; ?> value="<? echo $theme['id']; ?>" <? if (!$theme['actif']) echo 'style="color:#a60000;"'; ?>><? echo ploopi_htmlentities($theme['libelle']); ?><? if (!$theme['actif']) echo ' (inactif)'; ?></option>
                        <?
                    }
                    ?>
                </select>
            </p>
            <p>
                <label>Libellé:</label>
                <input name="espacedoc_sstheme_libelle" type="text" class="text" value="<? echo ploopi_htmlentities($sstheme->fields['libelle']); ?>">
            </p>
            <p onclick="javascript:ploopi_checkbox_click(event,'espacedoc_sstheme_actif');">
                <label for="espacedoc_sstheme_actif">Actif:</label>
                <input name="espacedoc_sstheme_actif" id="espacedoc_sstheme_actif" class="checkbox" type="checkbox" value="1" <? if ($sstheme->fields['actif']) echo 'checked'; ?> tabindex="111" />
            </p>
        </div>
        <div style="padding:4px;text-align:right;">
            <input type="reset" class="button" value="Réinitialiser" />
            <input type="submit" class="button" value="Enregistrer" />
        </div>
        </form>
        <iframe class="syn_iframe" name="espacedoc_sstheme_ajouter" src="./img/blank.gif"></iframe>
        <?
        $content = ob_get_contents();
        ob_end_clean();

        include_once './modules/espacedoc/include/global.php';

        $titre = ($_REQUEST['ploopi_op'] == 'espacedoc_sstheme_ajouter') ? 'Ajout' : 'Modification';

        echo $skin->create_popup("{$titre} d'un élément", $content, 'popup_sstheme');
        ploopi_die();
    break;
}
?>
