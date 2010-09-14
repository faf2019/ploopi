<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
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

include_once './include/classes/data_object_collection.php';
include_once './modules/forms/classes/formsForm.php';

echo $skin->open_simplebloc(_FORMS_LIST);

$intTsToday = ploopi_createtimestamp();

$objDOC = new data_object_collection('formsForm');
$objDOC->add_where("id_module = %d", $_SESSION['ploopi']['moduleid']);
$objDOC->add_where("(pubdate_start <= %s OR pubdate_start = '')", $intTsToday);
$objDOC->add_where("(pubdate_end >= %s OR pubdate_end = '')", $intTsToday);
$objDOC->add_where("id_workspace IN (%e)", array(explode(',', ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']))));

foreach($objDOC->get_objects() as $objForm)
{
    if (!$objForm->fields['option_adminonly'] || ploopi_isactionallowed(_FORMS_ACTION_ADMIN))
    {
    	?>
    	<a class="forms_public_link" href="<? echo ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}"); ?>">
    	<div>
            <h1><? echo htmlentities($objForm->fields['label']); ?></h1>
            <div><? echo ploopi_nl2br(htmlentities($objForm->fields['description'])); ?></div>
    	</div>
    	</a>
    	<?
    }
}
?>


<?
echo $skin->close_simplebloc();
?>
