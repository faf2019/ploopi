<?php
/**
 * Public / Derniers documents
 * 
 * @package espacedoc
 * @subpackage public
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

$intMaxReponse = 30;
?>

<div class="espacedoc_titre">
<h1>Dernières mises en ligne</h1>
<h2><? echo $intMaxReponse; ?> derniers documents</h2>
</div>

<?
if (isset($_REQUEST['espacedoc_saved']))
{
    ?>
    <div id="espacedoc_saved" style="display:none;">
        <div style="padding:20px 4px;text-align:center;color:#00a600;font-weight:bold;border:2px solid #143477;">
        document enregistré
        </div>
    </div>
    <script type="text/javascript">
        ploopi_window_onload_stock(
            function() {
                ploopi_showpopup($('espacedoc_saved').innerHTML, 250, null, true, 'popup_espacedoc_saved', null, 230);
                new PeriodicalExecuter( function(pe) { 
                        ploopi_hidepopup('popup_espacedoc_saved');
                        pe.stop();
                    }
                    ,2
                );
            }
        );
    </script>
    <?
}

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
    
    ORDER BY    timestp_create DESC
    LIMIT       0, {$intMaxReponse}
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
        'orderby_default' => 'date',
        'sort_default' => 'DESC'
    )
);
?>