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

/**
 * Liste des outils "système" disponibles
 * 
 * @package system
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */

echo $skin->open_simplebloc(_SYSTEM_LABEL_TOOLS);

$columns = array();
$values = array();

$columns['left']['tool'] = 
    array(
        'label' => 'Outil', 
        'width' => '200', 
        'options' => array('sort' => true)
    );
    
$columns['auto']['desc'] = 
    array(
        'label' => 'Description', 
        'options' => array('sort' => true)
    );

$c = 0;

$values[$c]['values']['tool'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/tools/tool_phpinfo.png\"><span>&nbsp;"._SYSTEM_LABEL_PHPINFO."</span>", 'style' => '', 'sort_label' => _SYSTEM_LABEL_PHPINFO);
$values[$c]['values']['desc'] = array('label' => _SYSTEM_EXPLAIN_PHPINFO, 'style' => '');
$values[$c]['description'] = _SYSTEM_EXPLAIN_PHPINFO;
$values[$c]['link'] = ploopi_urlencode("admin.php?op=phpinfo");
$c++;

$values[$c]['values']['tool'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/tools/tool_diagnostic.png\"><span>&nbsp;"._SYSTEM_LABEL_DIAGNOSTIC."</span>", 'style' => '', 'sort_label' => _SYSTEM_LABEL_DIAGNOSTIC);
$values[$c]['values']['desc'] = array('label' => _SYSTEM_EXPLAIN_DIAGNOSTIC, 'style' => '');
$values[$c]['description'] = _SYSTEM_EXPLAIN_DIAGNOSTIC;
$values[$c]['link'] = ploopi_urlencode("admin.php?op=diagnostic");
$c++;

$values[$c]['values']['tool'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/tools/tool_sqldump.png\"><span>&nbsp;"._SYSTEM_LABEL_SQLDUMP."</span>", 'style' => '', 'sort_label' => _SYSTEM_LABEL_SQLDUMP);
$values[$c]['values']['desc'] = array('label' => _SYSTEM_EXPLAIN_SQLDUMP, 'style' => '');
$values[$c]['description'] = _SYSTEM_EXPLAIN_SQLDUMP;
$values[$c]['link'] = ploopi_urlencode("admin.php?op=sqldump");
$c++;

$values[$c]['values']['tool'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/tools/tool_logusers.png\"><span>&nbsp;"._SYSTEM_LABEL_CONNECTEDUSERS."</span>", 'style' => '', 'sort_label' => _SYSTEM_LABEL_CONNECTEDUSERS);
$values[$c]['values']['desc'] = array('label' => _SYSTEM_EXPLAIN_CONNECTEDUSERS, 'style' => '');
$values[$c]['description'] = _SYSTEM_EXPLAIN_CONNECTEDUSERS;
$values[$c]['link'] = ploopi_urlencode("admin.php?op=connectedusers");
$c++;

$values[$c]['values']['tool'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/tools/tool_serverload.png\"><span>&nbsp;"._SYSTEM_LABEL_SERVERLOAD."</span>", 'style' => '', 'sort_label' => _SYSTEM_LABEL_SERVERLOAD);
$values[$c]['values']['desc'] = array('label' => _SYSTEM_EXPLAIN_SERVERLOAD, 'style' => '');
$values[$c]['description'] = _SYSTEM_EXPLAIN_SERVERLOAD;
$values[$c]['link'] = ploopi_urlencode("admin.php?op=serverload");
$c++;

$values[$c]['values']['tool'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/tools/tool_logactions.png\"><span>&nbsp;"._SYSTEM_LABEL_ACTIONHISTORY."</span>", 'style' => '', 'sort_label' => _SYSTEM_LABEL_ACTIONHISTORY);
$values[$c]['values']['desc'] = array('label' => _SYSTEM_EXPLAIN_ACTIONHISTORY, 'style' => '');
$values[$c]['description'] = _SYSTEM_EXPLAIN_ACTIONHISTORY;
$values[$c]['link'] = ploopi_urlencode("admin.php?op=actionhistory");
$c++;

$skin->display_array($columns, $values, 'array_tools', array('sortable' => true, 'orderby_default' => 'tool'));

echo $skin->close_simplebloc();
?>
