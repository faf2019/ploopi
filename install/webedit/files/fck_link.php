<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
?>
<?

// par défaut on prend le module WEBEDIT sur lequel on est déjà
if (isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['moduletype']) && $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['moduletype'] == 'webedit')
{
    $webedit_idm = $_SESSION['ploopi']['moduleid'];
}
else // sinon on va chercher le 1er dispo dans les modules accessibles depuis l'espace de travail courant.
{
    $webedit_idm = 0;
    foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm)
    {
        if (isset($_SESSION['ploopi']['modules'][$idm]['active']) && $_SESSION['ploopi']['modules'][$idm]['active'] && $_SESSION['ploopi']['modules'][$idm]['label'] == 'WEBEDIT') $webedit_idm = $idm;
    }
}

if ($webedit_idm)
{
    ploopi_init_module('webedit');

    include_once './modules/webedit/class_article.php';
    include_once './modules/webedit/class_heading.php';

    $headings = webedit_getheadings($webedit_idm);
    $articles = webedit_getarticles($webedit_idm);

    switch($ploopi_op)
    {
        case 'webedit_detail_heading';
            echo webedit_build_tree($headings, $articles, $_GET['hid'], $_GET['str'], (isset($_GET['option'])) ? $_GET['option'] : '');
            ploopi_die();
        break;

        case 'webedit_selectlink':
            ?>
            <script type="text/javascript">
            function webedit_showheading(hid, str, option)
            {
                if (typeof(option) == 'undefined') var option = '';
                
                
                elt = $('webedit_plus'+option+hid);
            
                if (elt.style.background.indexOf('plusbottom') != -1) elt.style.background = elt.style.background.replace('plusbottom', 'minusbottom');
                else  if (elt.style.background.indexOf('minusbottom')  != -1) elt.style.background = elt.style.background.replace('minusbottom', 'plusbottom');
                else  if (elt.style.background.indexOf('plus')  != -1) elt.style.background = elt.style.background.replace('plus', 'minus');
                else  if (elt.style.background.indexOf('minus')  != -1) elt.style.background = elt.style.background.replace('minus', 'plus');
            
                if (elt = $('webedit_dest'+option+hid))
                {
                    if (elt.style.display == 'none')
                    {
                        if (elt.innerHTML.length < 10) ploopi_xmlhttprequest_todiv('admin-light.php','op=xml_detail_heading&hid='+hid+'&str='+str+'&option='+option,'','webedit_dest'+option+hid);
                        elt.style.display='block';
                    }
                    else
                    {
                        elt.style.display='none';
                    }
                }
            }

            </script>
            <?
            echo webedit_build_tree($headings, $articles, 0, '', 'selectlink');
        break;
    }

}
else echo "Aucun module WEBEDIT disponible";
?>
