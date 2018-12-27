<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Définition de quelques variables javascript liées à php
 *
 * @package ploopi
 * @subpackage javascript
 * @copyright Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 */

?>

_PLOOPI_ENV = '<?php echo $_SESSION['ploopi']['env']; ?>';

var lstmsg = new Array();
lstmsg[0] = "<?php echo _PLOOPI_JS_EMAIL_ERROR ?>";
lstmsg[4] = "<?php echo _PLOOPI_JS_STRING_ERROR ?>";
lstmsg[5] = "<?php echo _PLOOPI_JS_INT_ERROR ?>";
lstmsg[6] = "<?php echo _PLOOPI_JS_FLOAT_ERROR ?>";
lstmsg[7] = "<?php echo _PLOOPI_JS_DATE_ERROR ?>";
lstmsg[8] = "<?php echo _PLOOPI_JS_TIME_ERROR ?>";
lstmsg[9] = "<?php echo _PLOOPI_JS_CHECK_ERROR ?>";
lstmsg[10] = "<?php echo _PLOOPI_JS_COLOR_ERROR ?>";
lstmsg[11] = "<?php echo _PLOOPI_JS_PHONE_ERROR ?>";
lstmsg[12] = "<?php echo _PLOOPI_JS_CAPTCHA_ERROR ?>";
lstmsg[13] = "<?php echo _PLOOPI_JS_WEB_ERROR ?>";
lstmsg[14] = "<?php echo _PLOOPI_JS_ONECHECK_ERROR ?>";
