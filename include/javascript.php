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
defaultStatus = '<? echo addslashes($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']); ?>';

var lstmsg = new Array();
lstmsg[0] = "<? echo _PLOOPI_JS_EMAIL_ERROR_1 ?>";
lstmsg[1] = "<? echo _PLOOPI_JS_EMAIL_ERROR_2 ?>";
lstmsg[2] = "<? echo _PLOOPI_JS_EMAIL_ERROR_3 ?>";
lstmsg[3] = "<? echo _PLOOPI_JS_EMAIL_ERROR_4 ?>";
lstmsg[4] = "<? echo _PLOOPI_JS_STRING_ERROR ?>";
lstmsg[5] = "<? echo _PLOOPI_JS_INT_ERROR ?>";
lstmsg[6] = "<? echo _PLOOPI_JS_FLOAT_ERROR ?>";
lstmsg[7] = "<? echo _PLOOPI_JS_DATE_ERROR ?>";
lstmsg[8] = "<? echo _PLOOPI_JS_TIME_ERROR ?>";
lstmsg[9] = "<? echo _PLOOPI_JS_CHECK_ERROR ?>";
lstmsg[10] = "<? echo _PLOOPI_JS_COLOR_ERROR ?>";

var error_bgcolor = "<? echo (isset($skin->values['colerror'])) ? $skin->values['colerror'] : "#FFAAAA"; ?>";
