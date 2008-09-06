<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2008 HeXad
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Fonctions javascript dynamiques
 *
 * @package rss
 * @subpackage javascript
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Fonctions javascript de validation de formulaire
 */

?>

function rssfeed_validate(form)
{
    if (ploopi_validatefield("<? echo _RSS_LABEL_FEEDURL; ?>",form.rssfeed_url,"string"))
    if (ploopi_validatefield("<? echo _RSS_LABEL_LIMIT; ?>",form.rssfeed_limit,"int")) 
       return true;
       
    return false;
}

function rsscat_validate(form)
{
    if (ploopi_validatefield("<? echo _RSS_LABEL_TITLE; ?>",form.rsscat_title,"string")) 
    if (ploopi_validatefield("<? echo _RSS_LABEL_LIMIT; ?>",form.rsscat_limit,"int")) 
       return true;

    return false;
}

function rssfilter_validate(form)
{
    if (ploopi_validatefield("<? echo _RSS_LABEL_TITLE; ?>",form.rssfilter_title,"string")) 
    if (ploopi_validatefield("<? echo _RSS_LABEL_LIMIT; ?>",form.rssfilter_limit,"int"))
       return true;
    
    return false;
}

function rssfilter_element_validate()
{
   <?php ploopi_init_module('rss'); ?>

   if(ploopi_validatefield("<? echo _RSS_LABEL_FILTER_VALUE_TEST; ?>", $('rss_element_value'), $('type_control').value)) return true;
   
   return false;
}

<?php
/**
 * Fonction de gestion comparateur de la condition en fonction de la cible 
 */
 ?>
function rss_select_target(target,target2,typeTarget)
{    <?php
    ploopi_init_module('rss');

    include_once './modules/rss/class_rss_filter_element.php';
  
    $objFilterElement = new rss_filter_element();
    
    $arrTabTarget  = $objFilterElement->getTabTarget();
    $arrTabCompare = $objFilterElement->getTabCompare();
    
    ?>
    var content = 'error';
    
    var tabtarget = new Array();
    var tabcompare = new Array();

    var typeCompare;
    var i;
    
    <?php
    /* Correspondance target => format de comparaison */ 
    foreach($arrTabTarget as $nameTarget => $arrTarget)
    {
      echo 'tabtarget[\''.addslashes($nameTarget).'\'] = \''.$arrTarget['compare'].'\';'."\n";
    }
    
    foreach($arrTabCompare as $nameCompare => $arrCompare)
    {
      echo 'tabcompare[\''.addslashes($nameCompare).'\'] = new Array();'."\n";
      echo 'i = 0;'."\n";
      foreach($arrCompare as $nameType => $arrType)
      {
        echo 'tabcompare[\''.addslashes($nameCompare).'\'][i] = new Array();'."\n";
        echo 'tabcompare[\''.addslashes($nameCompare).'\'][i][\'compare\'] = \''.addslashes($nameType).'\';'."\n";
        echo 'tabcompare[\''.addslashes($nameCompare).'\'][i][\'label\'] = \''.addslashes($arrType['label']).'\';'."\n";
        echo 'i++;'."\n";
      }
    }
    ?>
    for(var i=0; i < tabcompare[tabtarget[typeTarget.value]].length; i++)
    {
      if(content == 'error') content = '<select id="rss_element_compare" name="rss_element_compare" style="width:100%;">'+"\n";
      content += '<option value="'+tabcompare[tabtarget[typeTarget.value]][i]['compare']+'">'+tabcompare[tabtarget[typeTarget.value]][i]['label']+'</option>'+"\n";
    }
    
    if(content != 'error') content += '</select>'+"\n";

    content2 = '<div id="div_type_control"></div>'
    ploopi_innerHTML(target, content);

    ploopi_innerHTML(target2, '<input type="hidden" id="type_control" value="'+tabtarget[typeTarget.value]+'" />');
}