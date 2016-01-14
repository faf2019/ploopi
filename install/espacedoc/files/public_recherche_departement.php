<?php
/**
 * Public / Recherche thémarique de document(s)
 * 
 * @package espacedoc
 * @subpackage public
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

$intMaxReponse = 250;

// INIT PATTERN de recherche
$arrSearchPattern = array();

// Récupération de la session si pas de nouvelle recherche
if (!isset($_REQUEST['espacedoc_recherche_document_intitule']) && !empty($_SESSION['espacedoc']['document_recherche'])) $arrSearchPattern = $_SESSION['espacedoc']['document_recherche'];
if (isset($_REQUEST['espacedoc_reset'])) $arrSearchPattern = array();

// Récupération des paramètres de la recherche
if (isset($_REQUEST['espacedoc_recherche_document_id_departement'])) $arrSearchPattern['id_departement'] = $_REQUEST['espacedoc_recherche_document_id_departement'];
if (isset($_REQUEST['espacedoc_recherche_document_id_theme'])) $arrSearchPattern['id_theme'] = $_REQUEST['espacedoc_recherche_document_id_theme'];
if (isset($_REQUEST['espacedoc_recherche_document_intitule'])) $arrSearchPattern['intitule'] = $_REQUEST['espacedoc_recherche_document_intitule'];
if (isset($_REQUEST['espacedoc_recherche_document_timestp_d'])) $arrSearchPattern['timestp_d'] = $_REQUEST['espacedoc_recherche_document_timestp_d'];
if (isset($_REQUEST['espacedoc_recherche_document_timestp_f'])) $arrSearchPattern['timestp_f'] = $_REQUEST['espacedoc_recherche_document_timestp_f'];

 
// Définition des paramètres par défaut si aucune session ou aucune recherche en cours
if (!isset($arrSearchPattern['id_departement'])) $arrSearchPattern['id_departement'] = '';
if (!isset($arrSearchPattern['id_theme'])) $arrSearchPattern['id_theme'] = '';
if (!isset($arrSearchPattern['intitule'])) $arrSearchPattern['intitule'] = '';
if (!isset($arrSearchPattern['timestp_d'])) $arrSearchPattern['timestp_d'] = '';
if (!isset($arrSearchPattern['timestp_f'])) $arrSearchPattern['timestp_f'] = '';

// Sauvegarde de la recherche en cours en session
$_SESSION['espacedoc']['document_recherche'] = $arrSearchPattern;
?>

<div class="espacedoc_titre">
<h1>Recherche thématique</h1>
</div>

<form action="<? echo ploopi_urlencode('admin.php'); ?>" method="post" enctype="multipart/form-data">
<div style="width:800px;margin:auto;border-width:0px 1px;border-style:solid;border-color:#a0a0a0;background:#f0f0f0;padding:6px;">
    <p class="espacedoc_va">
        <label>Département :</label>
        <select class="select" id="espacedoc_recherche_document_id_departement" name="espacedoc_recherche_document_id_departement" tabindex="101" style="width:300px;" onchange="javascript:espacedoc_theme_refresh('espacedoc_recherche_document_id_theme', this.value);">
        <option value="" style="font-style:italic;">(Choisir un département)</option>
        <?
        foreach($arrTheme as $id_th => $th)
        {
            ?>
            <option value="<? echo $id_th; ?>" <? if ($id_th == $arrSearchPattern['id_departement']) echo 'selected="selected"'; ?>><? echo ploopi_htmlentities($th['libelle']); ?></option>
            <?
        }
        ?>
        </select>
        
        <span style="margin-left:10px;"></span>

        <label>Thème :</label>
        <select class="select" id="espacedoc_recherche_document_id_theme" name="espacedoc_recherche_document_id_theme" tabindex="102" style="width:300px;">
            <option value="" style="font-style:italic;">(Choisir un thème)</option>
        </select>
    </p>    
    
    <p class="espacedoc_va">
        <label>Date de mise en ligne :</label>
        
        <span style="margin-left:10px;">entre le</span>
        <input type="text" class="text" name="espacedoc_recherche_document_timestp_d" id="espacedoc_recherche_document_timestp_d" value="<? echo ploopi_htmlentities($arrSearchPattern['timestp_d']); ?>" style="width:70px;" />
        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('espacedoc_recherche_document_timestp_d', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>

        <span style="margin-left:10px;">et le</span>
        <input type="text" class="text" name="espacedoc_recherche_document_timestp_f" id="espacedoc_recherche_document_timestp_f" value="<? echo ploopi_htmlentities($arrSearchPattern['timestp_f']); ?>" style="width:70px;" />
        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('espacedoc_recherche_document_timestp_f', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
    </p>
    
    <p class="espacedoc_va">
        <label>Mots clés présent dans l'intitulé du document :</label>
        <input type="text" class="text" id="espacedoc_recherche_document_intitule" name="espacedoc_recherche_document_intitule" value="<? echo ploopi_htmlentities($arrSearchPattern['intitule']); ?>" tabindex="110" style="width:370px;" />
    </p>    
    
    <div style="clear:both;text-align:right;padding:4px;">
        <input type="button" class="button" value="Réinitialiser" onclick="document.location.href='<? echo ploopi_urlencode("admin.php?espacedoc_reset"); ?>';">
        <input type="submit" class="button" value="Lancer la recherche">
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
         
            espacedoc_theme_refresh(
                'espacedoc_recherche_document_id_theme',
                '<? echo addslashes($arrSearchPattern['id_departement']); ?>', 
                '<? echo addslashes($arrSearchPattern['id_theme']); ?>'
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

if (!empty($arrSearchPattern['id_departement'])) $arrSqlFilter[] = "document.id_departement = '".$db->addslashes($arrSearchPattern['id_departement'])."'";
if (!empty($arrSearchPattern['id_theme'])) $arrSqlFilter[] = "document.id_theme = '".$db->addslashes($arrSearchPattern['id_theme'])."'";

if (!empty($arrSearchPattern['timestp_d'])) $arrSqlFilter[] = "document.timestp_create >= '".ploopi_local2timestamp($arrSearchPattern['timestp_d'])."'";
if (!empty($arrSearchPattern['timestp_f'])) $arrSqlFilter[] = "document.timestp_create <= '".substr(ploopi_local2timestamp($arrSearchPattern['timestp_f']),0,8)."235959'";

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
            
        $arrResult['columns']['left']['departement'] = 
            array( 
                'label' => 'Thème',
                'width' => '150',
                'options' => array('sort' => true)
            );
            
        $arrResult['columns']['left']['theme'] = 
            array( 
                'label' => 'Sous-thème',
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
            

        $db->query("
            SELECT      document.*,
                        departement.libelle as libelle_departement,
                        theme.libelle as libelle_theme,
                        user.login,
                        user.lastname,
                        user.firstname
                        
            FROM        ploopi_mod_espacedoc_document document
            
            LEFT JOIN   ploopi_mod_espacedoc_departement departement
            ON          departement.id = document.id_departement
            
            LEFT JOIN   ploopi_mod_espacedoc_theme theme
            ON          theme.id = document.id_theme

            LEFT JOIN   ploopi_user user
            ON          user.id = document.id_user
            
            WHERE       ".implode(' AND ', $arrSqlFilter)." 

            ORDER BY    libelle_departement, libelle_theme
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
                            'departement' => array('label' => $row['libelle_departement']),
                            'theme' => array('label' => $row['libelle_theme']),
                            'fichier' => array('label' => $row['fichier']),
                            'user' => array('label' => "{$row['lastname']} {$row['firstname']}")
                        ),
                    'description' => "Modifier le document '".$row['intitule']."'",
                    'link' => 'javascript:void(0);',
                    'onclick' => "espacedoc_element_consulter('document', '{$row['id']}', event, '800', true);"
                );
        }            

        
        $skin->display_array(
            $arrResult['columns'], 
            $arrResult['rows'], 
            'espacedoc_document_supprimer', 
            array(
                'sortable' => true, 
                'orderby_default' => 'departement'
            )
        );
    }
}
else // Pas de recherche
{
    ?>
    <div style="padding:10px;text-align:center;border-top:1px solid #a0a0a0;">Pour effectuer une recherche de document, utilisez les champs ci-dessus</div>
    <?
}
?>