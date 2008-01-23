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
//if (defined('_PLOOPI_ERROR_REPORTING')) {error_reporting(_PLOOPI_ERROR_REPORTING);}
if (!defined('_PLOOPI_DISPLAY_ERRORS')) define('_PLOOPI_DISPLAY_ERRORS', false);
if (!defined('_PLOOPI_MAIL_ERRORS')) define('_PLOOPI_MAIL_ERRORS', false);
if (!defined('_PLOOPI_ADMINMAIL')) define('_PLOOPI_ADMINMAIL', '');

global $ploopi_errors_msg;
global $ploopi_errors_vars;
global $ploopi_errors_nb;
global $ploopi_errors_level;
global $ploopi_errortype;
global $ploopi_errorlevel;

$ploopi_errors_msg = '';
$ploopi_errors_vars = '';
$ploopi_errors_nb = 0;
$ploopi_errors_level = 0;

$ploopi_errortype = array (
							E_ERROR          => 'Error',
							E_WARNING        => 'Warning',
							E_PARSE          => 'Parse Error',
							E_NOTICE         => 'Notice',
							E_CORE_ERROR     => 'Core Error',
							E_CORE_WARNING   => 'Core Warning',
							E_COMPILE_ERROR  => 'Compile Error',
							E_COMPILE_WARNING => 'Compile Warning',
							E_USER_ERROR     => 'User Error',
							E_USER_WARNING   => 'User Warning',
							E_USER_NOTICE    => 'User Notice'
							);

$ploopi_errorlevel = array (
							0 => 'OK',
							1 => 'WARNING',
							2 => 'CRITICAL ERROR'
							);


function ploopi_errorhandler($errno, $errstr, $errfile, $errline, $vars)
{
	global $ploopi_errors_msg;
	global $ploopi_errors_nb;
	global $ploopi_errors_vars;
	global $ploopi_errors_level;

	global $ploopi_errortype;
	global $ploopi_errorlevel;

	// translate error_level into "readable" array
	$bit = _PLOOPI_ERROR_REPORTING;
	$res = array();

	while ($bit > 0)
	{
	   for($i = 0, $n = 0; $i <= $bit; $i = 1 * pow(2, $n), $n++) {
		   $end = $i;
	   }
	   $res[] = $end;
	   $bit = $bit - $end;
	}



	// if error in error reporting levels
	if (in_array($errno,$res))
	{
		$ploopi_errors_nb++;

		if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR) $ploopi_errors_level = 2;
		else if (($errno == E_WARNING || $errno == E_NOTICE || $errno == E_USER_NOTICE) && $ploopi_errors_level < 2) $ploopi_errors_level = 1;

		if ($ploopi_errors_msg == '') $ploopi_errors_msg  = "[{$_SERVER['HTTP_HOST']}] le ".date("d-m-Y H:i:s (T)")."\n\nVersion PHP : ".PHP_VERSION."\nOS : ".PHP_OS."\n\n";

		$ploopi_errors_msg .= "\nType d'erreur : {$ploopi_errortype[$errno]}\nMessage : $errstr\nFichier : $errfile\nLigne : $errline\n";

		ob_start();
		print_r($vars);
		$ploopi_errors_vars = ob_get_contents();
		ob_end_clean();

		if (_PLOOPI_DISPLAY_ERRORS)
		{
			// display message
			echo 	"
					<div class=\"ploopi_error\">
					<b>{$ploopi_errortype[$errno]}</b> <span>$errstr</span> dans <b>$errfile</b> à la ligne <b>$errline</b>
					</div>
					";

			if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR)
			{
				if (_PLOOPI_MAIL_ERRORS && _PLOOPI_ADMINMAIL != '') mail(_PLOOPI_ADMINMAIL,"[{$ploopi_errorlevel[$ploopi_errors_level]}] sur [{$_SERVER['HTTP_HOST']}]", "$ploopi_errors_nb erreur(s) sur $ploopi_errors_msg\n\nDUMP:\n$ploopi_errors_vars");
				ploopi_die();
			}

		}
		else
		{
			// critical error
			if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR)
			{
				while (@ob_end_clean());
				echo '<html><body><div align="center">Une erreur est survenue sur le site.<br />Contactez l\'administrateur.</div></body></html>';
				if (_PLOOPI_MAIL_ERRORS && _PLOOPI_ADMINMAIL != '') mail(_PLOOPI_ADMINMAIL,"[{$ploopi_errorlevel[$ploopi_errors_level]}] sur [{$_SERVER['HTTP_HOST']}]", "$ploopi_errors_nb erreur(s) sur $ploopi_errors_msg\n\nDUMP:\n$ploopi_errors_vars");
				ploopi_die();
			}
		}
	}
}

set_error_handler("ploopi_errorhandler");

?>
