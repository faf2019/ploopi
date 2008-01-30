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


ob_start();
session_start();
session_destroy();
$_SESSION = array();

define ('_PLOOPI_DISPLAY_ERRORS', true);
define ('_PLOOPI_ERROR_REPORTING', E_ALL);

chdir('..');

include './include/errors.php';
include './include/global.php';
include './db/class_db_mysql.php';

if (file_exists('./config/config.php')) ploopi_redirect('../');
?>

<html>
<head>
	<title>Installation de PLOOPI v<? echo _PLOOPI_VERSION; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<style type="text/css">
	body
	{
		font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
		font-size: 11px;
		font-weight: none;
		color:#000000;
		margin: 0px 0px 0px 0px;
	}

	table
	{
		font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
		font-size: 11px;
		font-weight: none;
		color:#000000;
		margin: 0px 0px 0px 0px;
	}

	td.field
	{
		background-color:#FFFFFF;
		font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
		font-size: 11px;
		font-weight: none;
		color:#000000;
		text-align:left;
	}

	td.fieldvalidate
	{
		background-color:#FFFFFF;
		font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
		font-size: 11px;
		font-weight: none;
		color:#000000;
		text-align:right;
	}

	td.fieldtitle
	{
		background-color:#FFFFFF;
		font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
		font-size: 11px;
		font-weight: bold;
		color:#000000;
		text-align:right;
	}

	td.title
	{
		background-color:#DDDDDD;
		font-size: 12px;
		font-weight: bold;
	}

	td.title2
	{
		background-color:#F0F0F0;
		font-size: 11px;
		font-weight: bold;
	}

	td.warning
	{
		background-color:#FFFFFF;
		font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
		font-size: 11px;
		font-weight: bold;
		color:#880000;
		text-align:left;
	}

	table.table
	{
		background-color:#000000;
	}

	input.field
	{
		background-color:#FFFFFF;
		font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
		font-size: 11px;
		font-weight: none;
		color:#000000;
		text-align:left;
		border-style: solid;
		border-width: 1px;
		border-color: #AAAAAA;
		padding: 2px;
	}

	input.button
	{
		background-color:#DDDDDD;
		font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
		font-size: 11px;
		font-weight: none;
		color:#000000;
		text-align:center;
		border-style: solid;
		border-width: 1px;
		border-color: #000000;
		padding: 2px;
	}

	a.link
	{
		color:#002BB8;
		text-decoration:none;
	}

	a.link:hover
	{
		text-decoration:underline;
	}

	.error
	{
		color:#a60000;
		font-weight:bold;
		padding:10px;
	}
	</style>
</head>
<body>
<table width="100%" height="100%">
	<tr>
		<td align="center" valign="middle">
		<?
		if (isset($_GET['end']))
		{
			echo 	'
					<b>FELICITATION</b>
					<br>
					<br>L\'installation est maintenant termin�e.
					<br>
					<br><b>Vous devez maintenant supprimer (ou renommer) le fichier ./config/install.php</b>
					<br>
					<br>vous pouvez vous connecter en utilisant votre compte "Administrateur"
					(login : admin / mot de passe : admin)
					<br>
					<br><a href="../index.php" class="link">Continuer</a>
					';
		}
		elseif (	isset($_POST['install'])
				&&	!empty($_POST['db_server'])
				&& 	!empty($_POST['db_database'])
				&& 	!empty($_POST['db_login'])
				&& 	isset($_POST['db_password'])
				&& 	!empty($_POST['admin_login'])
				&& 	!empty($_POST['admin_password'])
				&& 	!empty($_POST['secretkey'])
				&& 	!empty($_POST['pearpath'])
				)
		{

			$admin_password = empty($_POST['admin_password']) ? 'admin' : $_POST['admin_password'];

			$dbok = false;

			$db = new ploopi_db($_POST['db_server'], $_POST['db_login'], $_POST['db_password']);
			if($db->connection_id) // connexion ok
			{
				// try selecting database
				if ($db->selectdb($_POST['db_database'])) $dbok = true;
				else
				{
					$db->query("CREATE DATABASE {$_POST['db_database']}");
					$db = new ploopi_db($_POST['db_server'], $_POST['db_login'], $_POST['db_password'], $_POST['db_database']);
					if ($db->isconnected()) $dbok = true;
					else
					{
						echo 'Impossible de cr�er la base de donn�es � '.$_POST['db_database'].' �.<br><a href="install.php" class="link">Retour</a>';
					}
				}
			}
			else
			{
				echo 'Les param�tres de connexion � la base de donn�es sont erron�s.<br><a href="install.php" class="link">Retour</a>';
			}

			if($dbok)
			{
				$model_file = './config/config.php.model';
				$config_file = './config/config.php';
				$sql_file = './install/system/ploopi.sql';
				$content = '';

				if (file_exists($model_file))
				{
					if ($f = fopen( $model_file, "r" ))
					{
						while (!feof($f)) $content .= fgets($f, 4096);
						fclose($f);

						$tags = array(	'<DB_SERVER>',
										'<DB_DATABASE>',
										'<DB_LOGIN>',
										'<DB_PASSWORD>',
										'<USE_DBSESSION>',
										'<URL_ENCODE>',
										'<SECRETKEY>',
										'<FRONTOFFICE>',
										'<REWRITERULE>',
										'<PEARPATH>',
										'<INTERNETPROXY_HOST>',
										'<INTERNETPROXY_PORT>',
										'<INTERNETPROXY_USER>',
										'<INTERNETPROXY_PASS>'
									);
						$replacements = array(	$_POST['db_server'],
												$_POST['db_database'],
												$_POST['db_login'],
												$_POST['db_password'],
												($_POST['use_dbsession']) ? 'true' : 'false',
												($_POST['url_encode']) ? 'true' : 'false',
												$_POST['secretkey'],
												($_POST['frontoffice']) ? 'true' : 'false',
												($_POST['rewriterule']) ? 'true' : 'false',
												$_POST['pearpath'],
												$_POST['proxy_host'],
												$_POST['proxy_port'],
												$_POST['proxy_user'],
												$_POST['proxy_pass']
											);


						$content = str_replace($tags, $replacements, $content);

						if (is_writable('./config') && (is_writable('./config/config.php') || !file_exists('./config/config.php')))
						{
							if ($fc = fopen( $config_file, "w" ))
							{
								fwrite($fc, $content);
								fclose($fc);

								if (file_exists($sql_file))
								{
									$requests = array();
									$sql = '';

									$fs = fopen ($sql_file, "r");
									while (!feof($fs))
									{
										$sql .= fgets($fs, 4096);
									}
									fclose ($fs);

									$sql = trim($sql);
									$requests = explode(";\n",$sql);

									foreach ($requests AS $key => $request)
									{
										$request = trim($request);
										if ($request!='') $db->query($request);
									}

									$db->query("UPDATE `ploopi_user` SET `login` = '".$_POST['admin_login']."', `password` = '".md5("{$_POST['secretkey']}/{$_POST['admin_login']}/".md5($admin_password))."' WHERE  `login` = 'admin'");
								}
								else ploopi_die('Fichier SQL inexistant.<br><a href="install.php" class="link">Retour</a>');

								ploopi_redirect("install.php?end");
							}
							else ploopi_die('Impossible d\'�crire le fichier de configuration.<br><a href="install.php" class="link">Retour</a>');
						}
						else ploopi_die('Impossible d\'�crire le fichier de configuration.<br><a href="install.php" class="link">Retour</a>');

						fclose($f);
					}
					else ploopi_die('Fichier "mod�le" inaccessible.<br><a href="install.php" class="link">Retour</a>');
				}
				else ploopi_die('Fichier "mod�le" inexistant.<br><a href="install.php" class="link">Retour</a>');
			}


		}
		else
		{
			if (file_exists('config.php'))
			{
				?>
				<div class="error">
				<b />/!\ Il existe d�j� un fichier de configuration</b>
				<br />Vous devriez supprimer ou renommer le fichier � ./config/install.php �
				</div>
				<?
			}

			if (isset($_POST['install']))
			{
			?>
				<div class="error">
				/!\ Certains param�tres sont erron�s ou manquants
				</div>
			<?
			}
			?>

			<table cellpadding="4" cellspacing="1" class="table">
				<form action="install.php" method="post">
				<input type="hidden" name="install" value="" />
				<tr>
					<td class="title" colspan="2">Installation de PLOOPI <? echo _PLOOPI_VERSION; ?></td>
				</tr>
				<tr>
					<td class="title2" colspan="2">Param�trage � Base de Donn�es �</td>
				</tr>
				<tr>
					<td class="fieldtitle"><sup>* </sup>Serveur:</td>
					<td class="field"><input type="text" name="db_server" value="<? echo empty($_POST['db_server']) ? 'localhost' : htmlentities($_POST['db_server']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle"><sup>* </sup>Nom de la Base:</td>
					<td class="field"><input type="text" name="db_database" value="<? echo empty($_POST['db_database']) ? 'ploopi' : htmlentities($_POST['db_database']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle"><sup>* </sup>Utilisateur:</td>
					<td class="field"><input type="text" name="db_login" value="<? echo empty($_POST['db_login']) ? 'root' : htmlentities($_POST['db_login']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle">Mot de Passe:</td>
					<td class="field"><input type="password" name="db_password" value="<? echo empty($_POST['db_password']) ? '' : htmlentities($_POST['db_password']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="title2" colspan="2">Param�trage � PLOOPI �</td>
				</tr>
				<tr>
					<td class="fieldtitle"><sup>* </sup>Login Administrateur:</td>
					<td class="field"><input type="text" name="admin_login" value="<? echo empty($_POST['admin_login']) ? 'admin' : htmlentities($_POST['admin_login']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle"><sup>* </sup>Mot de Passe Administrateur:</td>
					<td class="field"><input type="text" name="admin_password" value="<? echo empty($_POST['admin_password']) ? 'admin' : htmlentities($_POST['admin_password']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle"><sup>* </sup>Phrase Secr�te:</td>
					<td class="field"><input type="text" name="secretkey" value="<? echo empty($_POST['secretkey']) ? 'ma phrase secr�te' : htmlentities($_POST['secretkey']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle">Encodage des URL visibles:</td>
					<td class="field">
						<select class="field" name="url_encode">
							<option value="true" <? echo (empty($_POST['url_encode']) || $_POST['url_encode']) ? 'selected' : ''; ?>>oui</option>
							<option value="false" <? echo (!empty($_POST['url_encode']) && !$_POST['url_encode']) ? 'selected' : ''; ?>>non</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldtitle">Stocker les Sessions en BDD:</td>
					<td class="field">
						<select class="field" name="use_dbsession">
							<option value="true" <? echo (empty($_POST['use_dbsession']) || $_POST['use_dbsession']) ? 'selected' : ''; ?>>oui</option>
							<option value="false" <? echo (!empty($_POST['use_dbsession']) && !$_POST['use_dbsession']) ? 'selected' : ''; ?>>non</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldtitle"><sup>* </sup>Dossier PEAR:</td>
					<td class="field"><input type="text" name="pearpath" value="<? echo empty($_POST['pearpath']) ? '/usr/share/php' : htmlentities($_POST['pearpath']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="title2" colspan="2">Param�trage � FrontOffice �</td>
				</tr>
				<tr>
					<td class="fieldtitle">Activation:</td>
					<td class="field">
						<select class="field" name="frontoffice">
							<option value="true" <? echo (empty($_POST['frontoffice']) || $_POST['frontoffice']) ? 'selected' : ''; ?>>oui</option>
							<option value="false" <? echo (!empty($_POST['frontoffice']) && !$_POST['frontoffice']) ? 'selected' : ''; ?>>non</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldtitle">R��criture d'URL:</td>
					<td class="field">
						<select class="field" name="rewriterule">
							<option value="true" <? echo (!empty($_POST['rewriterule']) && $_POST['rewriterule']) ? 'selected' : ''; ?>>oui</option>
							<option value="false" <? echo (empty($_POST['rewriterule']) || !$_POST['rewriterule']) ? 'selected' : ''; ?>>non</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="title2" colspan="2">Param�trage � Proxy Internet � (option)</td>
				</tr>
				<tr>
					<td class="fieldtitle">Adresse:</td>
					<td class="field"><input type="text" name="proxy_host" value="<? echo (empty($_POST['proxy_host'])) ? '' : htmlentities($_POST['proxy_host']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle">Port:</td>
					<td class="field"><input type="text" name="proxy_port" value="<? echo (empty($_POST['proxy_port'])) ? '' : htmlentities($_POST['proxy_port']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle">Utilisateur:</td>
					<td class="field"><input type="text" name="proxy_user" value="<? echo (empty($_POST['proxy_user'])) ? '' : htmlentities($_POST['proxy_user']); ?>" class="field" /></td>
				</tr>
				<tr>
					<td class="fieldtitle">Mot de Passe:</td>
					<td class="field"><input type="text" name="proxy_pass" value="<? echo (empty($_POST['proxy_pass'])) ? '' : htmlentities($_POST['proxy_pass']); ?>" class="field" /></td>
				</tr>
				<!--tr>
					<td class="fieldtitle">Activation Site CMS:</td>
					<td class="field"><input type="checkbox" name="activecms"></td>
				</tr-->
				<tr>
					<?
						if (is_writable('./config'))
						{
						?>
						<td class="fieldvalidate" colspan="2"><sup>* </sup>Champs obligatoires&nbsp;&nbsp;<input type="submit" value="Installer" class="button"></td>
						<?
						}
						else
						{
						?>
						<td class="warning" colspan="2">Le dossier � <? echo realpath ('./config'); ?> �<br>n'est pas accessible en �criture par Apache.<br>Impossible de continuer la proc�dure d'installation</td>
						<?
						}
					?>
				</tr>
				</form>
			</table>

			<br>

			<table cellpadding="4" cellspacing="1" class="table">
				<tr>
					<td class="title">Pr�-requis</td>
				</tr>
				<tr>
					<td class="field">
					Ploopi v<? echo _PLOOPI_VERSION; ?> n�cessite pour fonctionner correctement les applicatifs serveurs suivants :
					<br />- <a class="link" href="http://fr.wikipedia.org/wiki/Apache_HTTP_Server" target="_blank">Apache</a> 1.3+ ou 2.+
					<br />- <a class="link" href="http://fr.wikipedia.org/wiki/PHP_hypertext_preprocessor" target="_blank">PHP</a> 5.+
					<br />- <a class="link" href="http://fr.wikipedia.org/wiki/Mysql" target="_blank">MySQL</a> 5.+
					</td>
				</tr>
			</table>

			<br>
			<?
		}
		?>
		</td>
	</tr>
</table>
<?
?>
</body>
</html>