<?php
/**
 * Contenu javascript dynamique du module
 *
 * @package espacedoc
 * @subpackage javascript
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 *
 * @todo déplacer $arrTheme dans global.php
 */

global $db;

$jscript = 'var arrTheme = new Array(); var c = 0;';

/**
 * Tableau contenant les thèmes et sous-thèmes
 */

global $arrTheme;
$arrTheme = array();

$sql =  "
        SELECT      th.id as id_th,
                    sth.id as id_sth,
                    th.libelle as libelle_th,
                    sth.libelle as libelle_sth

        FROM        ploopi_mod_espacedoc_theme th

        LEFT JOIN   ploopi_mod_espacedoc_sstheme sth
        ON          th.id = sth.id_theme
        AND         sth.actif = 1

        WHERE       th.actif = 1

        ORDER BY    libelle_th,
                    libelle_sth
        ";

$db->query($sql);

while ($row = $db->fetchrow())
{
    if (empty($arrTheme[$row['id_th']]))
        $arrTheme[$row['id_th']] =
            array(
                'libelle' => $row['libelle_th']
            );

    if (!empty($row['id_sth']))
    {
        $arrTheme[$row['id_th']]['soustypes'][$row['id_sth']] =
            array (
                'libelle' => $row['libelle_sth']
            );

        $jscript .= "arrTheme[c] = new Array(4);";
        $jscript .= "arrTheme[c][0] = '{$row['id_th']}';";
        $jscript .= "arrTheme[c][1] = '{$row['id_sth']}';";
        $jscript .= "arrTheme[c][2] = '".addslashes($row['libelle_sth'])."';";
        $jscript .= "arrTheme[c][3] = 1;";
        $jscript .= "c++;";
    }
}

echo $jscript;
?>


var element_coche = false;

function espacedoc_element_cocher()
{
    element_coche = !element_coche;
    for (i=0;i<$$('input.espacedoc_element_checkbox').length;i++) $$('input.espacedoc_element_checkbox')[i].checked = element_coche;
}

function espacedoc_element_supprimer(type_element)
{
    element_list = '';
    for (i=0;i<$$('input.espacedoc_element_checkbox').length;i++)
    {
        if ($$('input.espacedoc_element_checkbox')[i].checked)
        {
            if (element_list != '') element_list += ',';
            element_list += $$('input.espacedoc_element_checkbox')[i].value;
        }
    }

    if (element_list != '')
    {
        if (confirm('Êtes-vous certain de vouloir supprimer ces éléments ?'))
        {
            document.location.href = 'admin-light.php?ploopi_env='+_PLOOPI_ENV+'&ploopi_op=espacedoc_'+type_element+'_supprimer&espacedoc_element_list='+element_list;
        }
    }
}

function espacedoc_element_ajouter(type_element, e, width)
{
    if (typeof(width) == 'undefined') width = 450;
    ploopi_showpopup(ploopi_ajaxloader_content, width, e, 'click', 'popup_'+type_element);
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=espacedoc_'+type_element+'_ajouter','popup_'+type_element);
}

function espacedoc_element_modifier(type_element, id, e, width, center)
{
    if (typeof(width) == 'undefined') width = 450;
    if (typeof(center) == 'undefined') center = false;

    if (center)
    {
        var posy = 250;
        if (e.pageY) posy = e.pageY;
        else if (e.clientY) posy = e.clientY + document.body.scrollTop;
        ploopi_showpopup(ploopi_ajaxloader_content, width, null, true, 'popup_'+type_element, null, posy);
    }
    else ploopi_showpopup(ploopi_ajaxloader_content, width, e, 'click', 'popup_'+type_element);
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=espacedoc_'+type_element+'_modifier&espacedoc_element_id='+id, 'popup_'+type_element);
}

function espacedoc_element_consulter(type_element, id, e, width, center)
{
    if (typeof(width) == 'undefined') width = 450;
    if (typeof(center) == 'undefined') center = false;

    if (center)
    {
        var posy = 250;
        if (e.pageY) posy = e.pageY;
        else if (e.clientY) posy = e.clientY + document.body.scrollTop;
        ploopi_showpopup(ploopi_ajaxloader_content, width, null, true, 'popup_'+type_element, null, posy);
    }
    else ploopi_showpopup(ploopi_ajaxloader_content, width, e, 'click', 'popup_'+type_element);
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=espacedoc_'+type_element+'_consulter&espacedoc_element_id='+id, 'popup_'+type_element);
}

/* THEME */

function espacedoc_theme_valider(form)
{
    if (ploopi_validatefield('Libelle',form.espacedoc_theme_libelle, 'string'))
        return true;

    return false;
}

/* SOUS THEME */

function espacedoc_sstheme_valider(form)
{
    if (ploopi_validatefield('<? echo ploopi_getparam('espacedoc_theme'); ?>',form.espacedoc_sstheme_id_theme, 'string'))
    if (ploopi_validatefield('Libelle',form.espacedoc_sstheme_libelle, 'string'))
        return true;

    return false;
}

/* MODELE DOCUMENT */

function espacedoc_modele_document_valider(form)
{
    if (ploopi_validatefield('Type',form.espacedoc_modele_document_type, 'string'))
    if (ploopi_validatefield('Intitulé',form.espacedoc_modele_document_libelle, 'string'))
        return true;

    return false;
}


/* AJOUT DOCUMENT */

function espacedoc_sstheme_refresh(dest, espacedoc_theme_id, espacedoc_sstheme_id) // 2e paramètre optionnel, permet de sélectionner un sous-thème
{
    while ($(dest).length>1) $(dest).remove(1);

    for (i=0; i<arrTheme.length; i++)
    {
        if (arrTheme[i][0] == espacedoc_theme_id)
        {
            $(dest).appendChild(newopt=document.createElement("OPTION"));
            newopt.value = arrTheme[i][1];
            if (arrTheme[i][3]) // actif
            {
                newopt.text = arrTheme[i][2];
            }
            else // inactif
            {
                newopt.text = arrTheme[i][2]+' (inactif)';
                newopt.style.color = '#a60000';
            }

            if (typeof(espacedoc_sstheme_id) != 'null' && espacedoc_sstheme_id == arrTheme[i][1]) $(dest).selectedIndex = $(dest).length-1;
        }
    }
}

function espacedoc_document_valider_modif(form)
{
    res = false;

    if (ploopi_validatefield('<? echo ploopi_getparam('espacedoc_theme'); ?>',form.espacedoc_document_id_theme, 'string'))
    if (ploopi_validatefield('<? echo ploopi_getparam('espacedoc_sstheme'); ?>',form.espacedoc_document_id_sstheme, 'string'))
    if (ploopi_validatefield('Intitulé du document',form.espacedoc_document_intitule, 'string'))
        res = true;

    return res;
}


function espacedoc_document_valider(form)
{
    res = false;

    if (ploopi_validatefield('<? echo ploopi_getparam('espacedoc_theme'); ?>',form.espacedoc_document_id_theme, 'string'))
    if (ploopi_validatefield('<? echo ploopi_getparam('espacedoc_sstheme'); ?>',form.espacedoc_document_id_sstheme, 'string'))
    if (ploopi_validatefield('Intitulé du document',form.espacedoc_document_intitule, 'string'))
    if (ploopi_validatefield('Fichier à télécharger',form._espacedoc_document_fichier, 'string'))
        res = true;

    return res;
}

function espacedoc_upload(sid)
{
    if ($('espacedoc_progressbar'))
    {
        $('espacedoc_progressbar').style.display = 'block';

        rc = ploopi_xmlhttprequest('index-quick.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=espacedoc_getstatus&sid='+sid);
        if (rc=='')
        {
            espacedoc_progressbar.setPercentage(100);
            $('espacedoc_progressbar_txt').innerHTML = '<b>Terminé</b>';
        }
        else
        {
            if (rc == 'notfound')
            {
                $('espacedoc_progressbar').style.display = 'none';
                alert("Impossible d'envoyer ce fichier,\nvérifiez qu'il n'est pas trop volumineux.");
            }
            else
            {
                rc = rc.split('|');

                if (rc.length == 6)
                {
                    // 0 : taille uploadée
                    // 1 : taille totale
                    // 2 : ?
                    // 3 : fichier en cours d'upload
                    // 4 : vitesse ko/s
                    // 5 : % avancement

                    espacedoc_progressbar.setPercentage(parseInt(rc[5]));
                    $('espacedoc_progressbar_txt').innerHTML = '<b>'+rc[5]+'%</b> ('+rc[0]+'/'+rc[1]+'ko)<br />Envoi de <b>'+rc[3]+'</b> à <i>'+rc[4]+' ko/s</i>';
                }

                setTimeout('espacedoc_upload(\''+sid+'\');',1000);
            }
        }
    }
}

var espacedoc_progressbar;
