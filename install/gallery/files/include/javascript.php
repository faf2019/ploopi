<?php
/*
    Copyright (c) 2009 HeXad
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
 * @package forms
 * @subpackage javascript
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

if (ploopi_ismoduleallowed('gallery'))
{
    if(!defined('_GALLERY_LABEL_SHOW')) ploopi_init_module('gallery',false,false,false);
    
    ?>
    function gallery_validate(form)
    {
        if (ploopi_validatefield("<?php echo _GALLERY_EDIT_LABEL_LABEL; ?>",form.gallery_label,"string")) 
            return(true);
        
        return(false);
    }
    
    function gallery_tpl_validate(form)
    {
        if (ploopi_validatefield("<?php echo _GALLERY_TPL_LABEL_NAME; ?>",form.gallery_tpl_block,"string")) 
            return(true);
        
        return(false);
    }
    <?php
}
?>