<?php
/**
 * Administration / Retrait de document(s)
 *
 * @package espacedoc
 * @subpackage public
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */
global $arrTheme;

$intMaxReponse = 250;

// INIT PATTERN de recherche
$arrSearchPattern = array();

// Récupération de la session si pas de nouvelle recherche
if (!isset($_REQUEST['espacedoc_recherche_document_intitule']) && !empty($_SESSION['espacedoc']["document_{$_SESSION['espacedoc']['espacedoc_documents_tab']}"])) $arrSearchPattern = $_SESSION['espacedoc']["document_{$_SESSION['espacedoc']['espacedoc_documents_tab']}"];
if (isset($_REQUEST['espacedoc_reset'])) $arrSearchPattern = array();

// Récupération des paramètres de la recherche
if (isset($_REQUEST['espacedoc_recherche_document_id_theme'])) $arrSearchPattern['id_theme'] = $_REQUEST['espacedoc_recherche_document_id_theme'];
if (isset($_REQUEST['espacedoc_recherche_document_id_sstheme'])) $arrSearchPattern['id_sstheme'] = $_REQUEST['espacedoc_recherche_document_id_sstheme'];
if (isset($_REQUEST['espacedoc_recherche_document_intitule'])) $arrSearchPattern['intitule'] = $_REQUEST['espacedoc_recherche_document_intitule'];

// Définition des paramètres par défaut si aucune session ou aucune recherche en cours
if (!isset($arrSearchPattern['id_theme'])) $arrSearchPattern['id_theme'] = '';
if (!isset($arrSearchPattern['id_sstheme'])) $arrSearchPattern['id_sstheme'] = '';
if (!isset($arrSearchPattern['intitule'])) $arrSearchPattern['intitule'] = '';

// Sauvegarde de la recherche en cours en session
$_SESSION['espacedoc']["document_{$_SESSION['espacedoc']['espacedoc_documents_tab']}"] = $arrSearchPattern;
?>

<div class="espacedoc_titre">
<?
if ($_SESSION['espacedoc']['espacedoc_documents_tab'] == 'supprimer')
{
    ?><h1>Retrait de documents</h1><?
}
else
{
    ?><h1>Modification d'un document</h1><?
}
?>
</div>

<form action="<? echo ploopi_urlencode('admin.php'); ?>" method="post" enctype="multipart/form-data">
<div style="width:800px;margin:auto;border-width:0px 1px;border-style:solid;border-color:#a0a0a0;background:#f0f0f0;padding:6px;">
    <p class="espacedoc_va">
        <label><? echo ploopi_getparam('espacedoc_theme'); ?> :</label>
        <select class="select" id="espacedoc_recherche_document_id_theme" name="espacedoc_recherche_document_id_theme" tabindex="101" style="width:300px;" onchange="javascript:espacedoc_sstheme_refresh('espacedoc_recherche_document_id_sstheme', this.value);">
        <option value="" style="font-style:italic;">(Choisir un élément)</option>
        <?
        foreach($arrTheme as $id_th => $th)
        {
            ?>
            <option value="<? echo $id_th; ?>" <? if ($id_th == $arrSearchPattern['id_theme']) echo 'selected="selected"'; ?>><? echo ploopi_htmlentities($th['libelle']); ?></option>
            <?
        }
        ?>
        </select>

        <span style="margin-left:10px;"></span>

        <label><? echo ploopi_getparam('espacedoc_sstheme'); ?> :</label>
        <select class="select" id="espacedoc_recherche_document_id_sstheme" name="espacedoc_recherche_document_id_sstheme" tabindex="102" style="width:300px;">
            <option value="" style="font-style:italic;">(Choisir un élément)</option>
        </select>
    </p>

    <p class="espacedoc_va">
        <label>Mots clés présents dans le document :</label>
        <input type="text" class="text" id="espacedoc_recherche_document_intitule" name="espacedoc_recherche_document_intitule" value="<? echo ploopi_htmlentities($arrSearchPattern['intitule']); ?>" tabindex="110" style="width:370px;" />
    </p>

    <div style="clear:both;text-align:right;padding:4px;">
        <input type="button" class="button" value="Réinitialiser" onclick="document.location.href='<? echo ploopi_urlencode("admin.php?espacedoc_reset"); ?>';">
        <input type="submit" class="button" value="Chercher">
    </div>
</div>
</form>

<div id="espacedoc_saved" style="display:none;">
    <div style="padding:20px 4px;text-align:center;color:#00a600;font-weight:bold;border:2px solid #143477;">
    document enregistré
    </div>
</div>

<script type="text/javascript">
    ploopi_window_onload_stock(
        function() {
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

            espacedoc_sstheme_refresh(
                'espacedoc_recherche_document_id_sstheme',
                '<? echo addslashes($arrSearchPattern['id_theme']); ?>',
                '<? echo addslashes($arrSearchPattern['id_sstheme']); ?>'
            );
        }
    );
</script>

<?
if (!empty($arrSearchPattern['intitule']))
{
    $arrRelevance = ploopi_search($arrSearchPattern['intitule'], _ESPACEDOC_OBJECT_DOCUMENT, '', $_SESSION['ploopi']['moduleid']);
    $arrSqlFilter[] = (empty($arrRelevance)) ? "document.id = 0" : "document.id IN (".implode(',', array_keys($arrRelevance)).")";
}

if (!empty($arrSearchPattern['id_theme'])) $arrSqlFilter[] = "document.id_theme = '".$db->addslashes($arrSearchPattern['id_theme'])."'";
if (!empty($arrSearchPattern['id_sstheme'])) $arrSqlFilter[] = "document.id_sstheme = '".$db->addslashes($arrSearchPattern['id_sstheme'])."'";

if (!empty($arrSqlFilter)) // au moins un critère de recherche défini => on continue
{
    $db->query("
        SELECT      count(document.id) as total
        FROM        ploopi_mod_espacedoc_document document
        WHERE       ".implode(' AND ', $arrSqlFilter)."
    ");

    $row = $db->fetchrow();
    ?>

    <div class="espacedoc_toolbar" style="border-top:1px solid #a0a0a0;">
        <?
        if ($_SESSION['espacedoc']['espacedoc_documents_tab'] == 'supprimer')
        {
            ?>
            <div style="float:right;">
                <a href="javascript:void(0);" onclick="javascript:espacedoc_element_cocher();"><img src="./modules/espacedoc/img/ico_checkbox.png">Cocher/Décocher tout</a>
                <a href="javascript:void(0);" onclick="javascript:espacedoc_element_supprimer('document');"><img src="./modules/espacedoc/img/ico_trash.png">Supprimer les éléments cochés</a>
            </div>
            <?
        }
        ?>
        <div style="float:left;">&nbsp;<b><? echo $row['total']; ?> document(s)</b> correspondent à vos critères de recherche</div>
    </div>
    <?
    if ($row['total'] > $intMaxReponse)
    {
        ?>
        <div style="padding:10px;text-align:center;font-weight:bold;color:#800000;border-top:1px solid #a0a0a0;">Il y a trop de réponses (<? echo $intMaxReponse; ?> max), vous devriez préciser votre recherche</div>
        <?
    }
    else
    {
        $arrResult =
            array(
                'columns' => array(),
                'rows' => array()
            );

        $arrResult['columns']['left']['theme'] =
            array(
                'label' => ploopi_getparam('espacedoc_theme'),
                'width' => '150',
                'options' => array('sort' => true)
            );

        $arrResult['columns']['left']['sstheme'] =
            array(
                'label' => ploopi_getparam('espacedoc_sstheme'),
                'width' => '150',
                'options' => array('sort' => true)
            );

        $arrResult['columns']['auto']['intitule'] =
            array(
                'label' => 'Intitulé',
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['user'] =
            array(
                'label' => 'Par',
                'width' => '150',
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['date'] =
            array(
                'label' => 'Date mise en ligne',
                'width' => '150',
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['fichier'] =
            array(
                'label' => 'Nom du document',
                'width' => '200',
                'options' => array('sort' => true)
            );

        if ($_SESSION['espacedoc']['espacedoc_documents_tab'] == 'supprimer')
        {
            $arrResult['columns']['actions_right']['actions'] =
                array(
                    'label' => '&nbsp;',
                    'width' => '24'
                );
        }

        $db->query("
            SELECT      document.*,
                        theme.libelle as libelle_theme,
                        sstheme.libelle as libelle_sstheme,
                        user.login,
                        user.lastname,
                        user.firstname

            FROM        ploopi_mod_espacedoc_document document

            LEFT JOIN   ploopi_mod_espacedoc_theme theme
            ON          theme.id = document.id_theme

            LEFT JOIN   ploopi_mod_espacedoc_sstheme sstheme
            ON          sstheme.id = document.id_sstheme

            LEFT JOIN   ploopi_user user
            ON          user.id = document.id_user

            WHERE       ".implode(' AND ', $arrSqlFilter)."

            ORDER BY    libelle_theme, libelle_sstheme
        ");


        while ($row = $db->fetchrow())
        {
            $arrDate = ploopi_timestamp2local($row['timestp_create']);



            $arrResult['rows'][] =
                array(
                    'values' =>
                        array(
                            'intitule' => array('label' => $row['intitule']),
                            'date' => array('label' => "{$arrDate['date']} {$arrDate['time']}", 'sort_label' => $row['timestp_create']),
                            'theme' => array('label' => $row['libelle_theme']),
                            'sstheme' => array('label' => $row['libelle_sstheme']),
                            'fichier' => array('label' => $row['fichier']),
                            'user' => array('label' => "{$row['lastname']} {$row['firstname']}"),
                            'actions' => array('label' => ($row['id_user'] == $_SESSION['ploopi']['userid'] || ploopi_isactionallowed(_ESPACEDOC_ACTION_ADMIN)) ? '<input type="checkbox" style="cursor:pointer;" class="espacedoc_element_checkbox" value="'.$row['id'].'">' : '&nbsp;')
                        ),
                    'description' => ($_SESSION['espacedoc']['espacedoc_documents_tab'] == 'supprimer' ? 'Consulter' : 'Modifier'). " le document '".$row['intitule']."'",
                    'link' => 'javascript:void(0);',
                    'onclick' => "espacedoc_element_".(($_SESSION['espacedoc']['espacedoc_documents_tab'] != 'supprimer' && ($row['id_user'] == $_SESSION['ploopi']['userid'] || ploopi_isactionallowed(_ESPACEDOC_ACTION_ADMIN))) ? 'modifier' : 'consulter')."('document', '{$row['id']}', event, '800', true);"
                );
        }


        $skin->display_array(
            $arrResult['columns'],
            $arrResult['rows'],
            'espacedoc_document_supprimer',
            array(
                'sortable' => true,
                'orderby_default' => 'theme'
            )
        );
    }
}
else // Pas de recherche
{
    ?>
    <div style="padding:10px;text-align:center;border-top:1px solid #a0a0a0;">
        <?
        if ($_SESSION['espacedoc']['espacedoc_documents_tab'] == 'supprimer')
        {
            ?>Pour effectuer un retrait de document, utilisez les champs ci-dessus<?
        }
        else
        {
            ?>Pour effectuer une modification de document, utilisez les champs ci-dessus<?
        }
        ?>
    </div>
    <?
}
?>
