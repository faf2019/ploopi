<?php
/*
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
 * Fichier de langue 'anglais' utilisé durant la procédure d'installation de Ploopi.
 *
 * @package ploopi
 * @subpackage install
 * @copyright Ovensia, Hexad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Définition des constantes
 */

define ('_PLOOPI_INSTALL_TEXT',             'Welcome in the installation of PLOOPI...');

define ('_PLOOPI_INSTALL_TITLE',            'PLOOPI Installation');

define ('_PLOOPI_INSTALL_SELECT_LANGUAGE',  'Selection of language');
define ('_PLOOPI_INSTALL_CTRL_REQUESTED',   'Control of requested minimums');
define ('_PLOOPI_INSTALL_PARAM_DB',         'Definition of parameters Database');
define ('_PLOOPI_INSTALL_PARAM_SITE',       'Personalization of the site');

define ('_PLOOPI_INSTALL_CHOOSE_LANGUAGE',  'Choose the language of installation');

define ('_PLOOPI_INSTALL_NEXT_BUTTON',      'Next stage >>');
define ('_PLOOPI_INSTALL_PREC_BUTTON',      '<< Previous stage');
define ('_PLOOPI_INSTALL_REFRESH_BUTTON',   'Update');
define ('_PLOOPI_INSTALL_FINISH_BUTTON',    'Finish');

define ('_PLOOPI_INSTALL_ICO_OK',           '/gfx/p_green.png');
define ('_PLOOPI_INSTALL_ICO_ERREUR',       '/gfx/p_red.png');

define ('_PLOOPI_ERROR_JAVASCRIPT',            'WARNING : Ploopi nÃ©cessite l\'activation de Javascript');
define ('_PLOOPI_CTRL_CONFIG_WRITE',           'Writing in the directory "config"');
define ('_PLOOPI_CTRL_CONFIG_WRITE_SUGGEST',   'You must give rights to apache in writing on the directory "config" <code>sudo chown -R www-data:www-data /var/www/ploopi/config</code>');
define ('_PLOOPI_CTRL_DATA_WRITE',             'Writing in the directory "data"');
define ('_PLOOPI_ERROR_DATA_WRITE_SUGGEST',    'You must give rights to apache in writing on the directory "data"<code>sudo chown -R www-data:www-data /var/www/ploopi/data</code>');
define ('_PLOOPI_ERROR_DATA_WRITE_WARNING',    'WARNING : The directory "data" will contain all your files (except database). It is therefore hard recommended to locate "data" out of PLOOPLI and on a reassured disc (raid, regular maintenances,..)');

?>