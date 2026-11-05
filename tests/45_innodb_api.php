<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Migration réelle et contrat HTTP de l'API : autorisations, isolation, cycle des clés et administration.
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
require __DIR__.'/bootstrap.php';
exiger_instance();
amorcer_socle();

$arrApiKeyIds = array();
$intApiUser = 0;
$intApiModule = 0;
$intApiType = 0;
$booApiTypeCreated = false;
$booApiFilesCreated = false;
$intApiNewsModule = 0;
$intApiNewsType = 0;
$booApiNewsTypeCreated = false;
$booApiNewsFilesCreated = false;
function api_test_cleanup()
{
    global $arrApiKeyIds, $intApiUser, $intApiModule, $intApiType, $booApiTypeCreated, $booApiFilesCreated, $intApiNewsModule, $intApiNewsType, $booApiNewsTypeCreated, $booApiNewsFilesCreated;
    foreach ($arrApiKeyIds as $intId)
    {
        db()->query('DELETE FROM ploopi_api_request WHERE id_key = '.(int) $intId);
        db()->query('DELETE FROM ploopi_api_key WHERE id = '.(int) $intId);
    }
    if ($intApiModule)
    {
        db()->query('DELETE FROM ploopi_mod_wiki_page WHERE id_module = '.(int) $intApiModule);
        db()->query('DELETE FROM ploopi_module_workspace WHERE id_module = '.(int) $intApiModule);
        db()->query('DELETE FROM ploopi_module WHERE id = '.(int) $intApiModule);
    }
    if ($intApiNewsModule)
    {
        db()->query('DELETE FROM ploopi_mod_news2_entry WHERE id_module = '.(int) $intApiNewsModule);
        db()->query('DELETE FROM ploopi_mod_news2_cat WHERE id_module = '.(int) $intApiNewsModule);
        db()->query('DELETE FROM ploopi_module_workspace WHERE id_module = '.(int) $intApiNewsModule);
        db()->query('DELETE FROM ploopi_module WHERE id = '.(int) $intApiNewsModule);
    }
    if ($booApiNewsTypeCreated) db()->query('DELETE FROM ploopi_module_type WHERE id = '.(int) $intApiNewsType);
    if ($booApiNewsFilesCreated) ploopi\fs::deletedir(PLOOPI_ROOT.'/modules/news2');
    if ($intApiUser)
    {
        db()->query('DELETE FROM ploopi_workspace_user WHERE id_user = '.(int) $intApiUser);
        db()->query('DELETE FROM ploopi_user WHERE id = '.(int) $intApiUser);
    }
    if ($booApiTypeCreated) db()->query('DELETE FROM ploopi_module_type WHERE id = '.(int) $intApiType);
    if ($booApiFilesCreated) ploopi\fs::deletedir(PLOOPI_ROOT.'/modules/wiki');
    db()->query('DROP TABLE IF EXISTS api_test_legacy');
}
register_shutdown_function('api_test_cleanup');

T::section('InnoDB : conversion, données préservées et transactions');
T::eq('installation neuve entièrement InnoDB', 0, (int) db()->query("SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND ENGINE <> 'InnoDB'")->fetch_assoc()['n']);
db()->query('CREATE TABLE api_test_legacy (id int PRIMARY KEY, label varchar(100)) ENGINE=MyISAM');
db()->query("INSERT INTO api_test_legacy VALUES (1, 'conservé')");
db()->query("UPDATE ploopi_module_type SET version = '2.0.0' WHERE id = 1");
ploopi\schema::upgrade(PLOOPI_ROOT.'/install/system/update_ploopi_2.1.0.sql', '2.1.0');
// Puis les mises à jour suivantes, dans l'ordre, comme Système → Mise à jour
$arrApiNextUpdates = array();
foreach (glob(PLOOPI_ROOT.'/install/system/update_ploopi_*.sql') as $strApiUpdate)
{
    $strApiVersion = substr(basename($strApiUpdate, '.sql'), strlen('update_ploopi_'));
    if (version_compare($strApiVersion, '2.1.0', '>')) $arrApiNextUpdates[$strApiVersion] = $strApiUpdate;
}
uksort($arrApiNextUpdates, 'version_compare');
foreach ($arrApiNextUpdates as $strApiVersion => $strApiUpdate) ploopi\schema::upgrade($strApiUpdate, $strApiVersion);
T::eq('une table métier sans préfixe est convertie', 'InnoDB', db()->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'api_test_legacy'")->fetch_assoc()['ENGINE']);
T::eq('version avancée après la migration', _PLOOPI_VERSION, db()->query('SELECT version FROM ploopi_module_type WHERE id = 1')->fetch_assoc()['version']);
T::eq('la donnée est préservée', 'conservé', db()->query('SELECT label FROM api_test_legacy')->fetch_assoc()['label']);
T::eq('une reprise ne convertit rien', array(), ploopi\schema::innodb());
db()->begin_transaction();
db()->query("UPDATE api_test_legacy SET label = 'annulé' WHERE id = 1");
db()->rollback();
T::eq('le rollback est effectif', 'conservé', db()->query('SELECT label FROM api_test_legacy')->fetch_assoc()['label']);
T::eq('aucune table persistante hors InnoDB', 0, (int) db()->query("SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND ENGINE <> 'InnoDB'")->fetch_assoc()['n']);

T::section('Fixtures : utilisateur ordinaire, module wiki et deux espaces');
if (!is_dir(PLOOPI_ROOT.'/modules/wiki'))
{
    ploopi\fs::copydir(PLOOPI_ROOT.'/install/wiki/files', PLOOPI_ROOT.'/modules/wiki');
    $booApiFilesCreated = true;
}
$resType = db()->query("SELECT id FROM ploopi_module_type WHERE label = 'wiki'");
$arrType = $resType->fetch_assoc();
if (!$arrType)
{
    $objType = new ploopi\data_object('ploopi_module_type');
    $objType->init_description();
    $objType->fields['label'] = 'wiki';
    $objType->fields['version'] = '1.4.13';
    $objType->save();
    $intApiType = (int) $objType->fields['id'];
    $booApiTypeCreated = true;
}
else $intApiType = (int) $arrType['id'];
$resTable = db()->query("SHOW TABLES LIKE 'ploopi_mod_wiki_page'");
if (!$resTable->num_rows)
    foreach (ploopi\db::split_sql(file_get_contents(PLOOPI_ROOT.'/install/wiki/structure.sql')) as $strSql) if (trim($strSql) !== '') db()->query($strSql);
$objUser = new ploopi\user();
$objUser->init_description();
$objUser->fields['login'] = 'api_fixture';
$objUser->fields['firstname'] = 'API';
$objUser->fields['password'] = ploopi\user::generate_hash('Api!Test2026', 'api_fixture');
$objUser->fields['disabled'] = 0;
$objUser->save();
$intApiUser = (int) $objUser->fields['id'];
db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intApiUser}, 2, 1, 10)");
$objModule = new ploopi\data_object('ploopi_module');
$objModule->init_description();
$objModule->fields['label'] = 'api_fixture';
$objModule->fields['id_module_type'] = $intApiType;
$objModule->fields['id_workspace'] = 2;
$objModule->fields['active'] = 1;
$objModule->save();
$intApiModule = (int) $objModule->fields['id'];
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace) VALUES ({$intApiModule}, 2)");
db()->query("INSERT INTO ploopi_mod_wiki_page (id, content, tags, id_module, id_workspace) VALUES ('api-visible', 'contenu visible', '', {$intApiModule}, 2), ('api-hidden', 'contenu secret autre espace', '', {$intApiModule}, 987654)");
// Deuxième fournisseur réel : les actualités non publiées ne sortent jamais.
if (!is_dir(PLOOPI_ROOT.'/modules/news2'))
{
    ploopi\fs::copydir(PLOOPI_ROOT.'/install/news2/files', PLOOPI_ROOT.'/modules/news2');
    $booApiNewsFilesCreated = true;
}
$arrType = db()->query("SELECT id FROM ploopi_module_type WHERE label = 'news2'")->fetch_assoc();
if (!$arrType)
{
    $objType = new ploopi\data_object('ploopi_module_type');
    $objType->init_description();
    $objType->fields['label'] = 'news2';
    $objType->fields['version'] = '3.3.9';
    $objType->save();
    $intApiNewsType = (int) $objType->fields['id'];
    $booApiNewsTypeCreated = true;
}
else $intApiNewsType = (int) $arrType['id'];
if (!db()->query("SHOW TABLES LIKE 'ploopi_mod_news2_entry'")->num_rows)
    foreach (ploopi\db::split_sql(file_get_contents(PLOOPI_ROOT.'/install/news2/structure.sql')) as $strSql) if (trim($strSql) !== '') db()->query($strSql);
$objNewsModule = new ploopi\data_object('ploopi_module');
$objNewsModule->init_description();
$objNewsModule->fields['label'] = 'api_fixture_news';
$objNewsModule->fields['id_module_type'] = $intApiNewsType;
$objNewsModule->fields['id_workspace'] = 2;
$objNewsModule->fields['active'] = 1;
$objNewsModule->save();
$intApiNewsModule = (int) $objNewsModule->fields['id'];
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace) VALUES ({$intApiNewsModule}, 2)");
db()->query("INSERT INTO ploopi_mod_news2_entry (title, published, id_module, id_workspace) VALUES ('api-published', 1, {$intApiNewsModule}, 2)");
$intApiPublishedId = db()->insert_id;
db()->query("INSERT INTO ploopi_mod_news2_entry (title, published, id_module, id_workspace) VALUES ('api-draft', 0, {$intApiNewsModule}, 2)");
$intApiDraftId = db()->insert_id;
$arrKey = ploopi\api_key::issue($intApiUser, 2, 'Clé test', array('core:read', 'wiki:read', 'news2:read'));
$arrApiKeyIds[] = $arrKey['id'];
$arrCoreKey = ploopi\api_key::issue($intApiUser, 2, 'Socle seul', array('core:read'));
$arrApiKeyIds[] = $arrCoreKey['id'];
T::vrai('la clé est générée', preg_match('/^plp_[a-f0-9]{64}$/D', $arrKey['token']) === 1);
$arrStored = db()->query('SELECT * FROM ploopi_api_key WHERE id = '.$arrKey['id'])->fetch_assoc();
T::eq('seule l’empreinte est stockée', hash('sha256', $arrKey['token']), $arrStored['token_hash']);
T::vrai('aucun secret en clair en base', !in_array($arrKey['token'], $arrStored, true));
try { ploopi\api_key::issue($intApiUser, 987654, 'Interdite', array('core:read')); T::vrai('clé hors espace refusée', false); }
catch (InvalidArgumentException $objError) { T::vrai('clé hors espace refusée', true); }

function api_test_get($strPath, $strKey = '', $mixPost = null)
{
    return H::req('/api/v1'.$strPath, $mixPost, $strKey === '' ? array() : array('Authorization: Bearer '.$strKey));
}
T::section('Authentification, isolation et lecture seule');
T::eq('anonyme refusé en JSON', 401, api_test_get('/me')['code']);
T::eq('clé en query string refusée', 401, api_test_get('/me?api_key='.$arrKey['token'])['code']);
$arrRep = api_test_get('/me', $arrKey['token']);
T::eq('clé valide acceptée', 200, $arrRep['code']);
T::eq('identité du propriétaire', $intApiUser, json_decode($arrRep['body'], true)['data']['id'] ?? 0);
T::contient('réponse non cachable', 'no-store', $arrRep['headers']);
T::contientpas('aucun cookie de session API', 'Set-Cookie:', $arrRep['headers']);
T::contientpas('aucun mot de passe dans le profil', 'password', $arrRep['body']);
T::eq('POST refusé', 405, api_test_get('/me', $arrKey['token'], 'x=1')['code']);
T::eq('route inconnue', 404, api_test_get('/unknown', $arrKey['token'])['code']);
$arrRep = api_test_get('/modules/'.$intApiModule.'/pages?per_page=1', $arrKey['token']);
T::eq('collection wiki accessible', 200, $arrRep['code']);
$arrJson = json_decode($arrRep['body'], true);
T::eq('seul l’espace de la clé est visible', 1, $arrJson['meta']['total'] ?? -1);
T::eq('pagination appliquée', 1, count($arrJson['data'] ?? array()));
T::contientpas('aucune fuite de l’autre espace', 'contenu secret', $arrRep['body']);
T::eq('détail autorisé', 200, api_test_get('/modules/'.$intApiModule.'/pages/api-visible', $arrKey['token'])['code']);
T::eq('détail hors espace invisible', 404, api_test_get('/modules/'.$intApiModule.'/pages/api-hidden', $arrKey['token'])['code']);
$arrRep = api_test_get('/modules/'.$intApiNewsModule.'/articles', $arrKey['token']);
T::eq('actualités accessibles', 200, $arrRep['code']);
T::eq('seule l’actualité publiée sort', 1, json_decode($arrRep['body'], true)['meta']['total'] ?? -1);
T::contientpas('aucun brouillon dans la collection', 'api-draft', $arrRep['body']);
T::eq('actualité publiée accessible en détail', 200, api_test_get('/modules/'.$intApiNewsModule.'/articles/'.$intApiPublishedId, $arrKey['token'])['code']);
T::eq('brouillon inaccessible en détail', 404, api_test_get('/modules/'.$intApiNewsModule.'/articles/'.$intApiDraftId, $arrKey['token'])['code']);
T::eq('catégories accessibles', 200, api_test_get('/modules/'.$intApiNewsModule.'/categories', $arrKey['token'])['code']);

T::eq('module inconnu invisible', 404, api_test_get('/modules/987654/pages', $arrKey['token'])['code']);
T::eq('permission de clé insuffisante', 403, api_test_get('/modules/'.$intApiModule.'/pages', $arrCoreKey['token'])['code']);
T::eq('pagination invalide refusée', 400, api_test_get('/modules/'.$intApiModule.'/pages?per_page=101', $arrKey['token'])['code']);
T::eq('pagination structurée refusée', 400, api_test_get('/modules/'.$intApiModule.'/pages?page[]=1', $arrKey['token'])['code']);
T::eq('injection dans l’identifiant sans effet', 404, api_test_get('/modules/'.$intApiModule.'/pages/'.rawurlencode("' OR 1=1 --"), $arrKey['token'])['code']);
$arrRep = api_test_get('/openapi.json', $arrKey['token']);
T::eq('OpenAPI accessible', 200, $arrRep['code']);
$arrSpec = json_decode($arrRep['body'], true);
T::eq('OpenAPI 3', '3.0.3', $arrSpec['openapi'] ?? '');
T::vrai('le fournisseur wiki documenté', isset($arrSpec['paths']['/modules/{module_id}/pages']));

T::section('Modifications des droits, compte et cycle de vie');
db()->query("UPDATE ploopi_user SET disabled = 1 WHERE id = {$intApiUser}");
T::eq('compte désactivé immédiatement refusé', 403, api_test_get('/me', $arrKey['token'])['code']);
db()->query("UPDATE ploopi_user SET disabled = 0 WHERE id = {$intApiUser}");
db()->query("UPDATE ploopi_user SET date_expire = 20000101000000 WHERE id = {$intApiUser}");
T::eq('compte expiré refusé', 403, api_test_get('/me', $arrKey['token'])['code']);
db()->query("UPDATE ploopi_user SET date_expire = 0 WHERE id = {$intApiUser}");
T::eq('compte rétabli accepté', 200, api_test_get('/me', $arrKey['token'])['code']);
db()->query("DELETE FROM ploopi_workspace_user WHERE id_user = {$intApiUser}");
T::eq('rattachement supprimé immédiatement refusé', 403, api_test_get('/me', $arrKey['token'])['code']);
db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intApiUser}, 2, 1, 10)");
db()->query("UPDATE ploopi_module SET active = 0 WHERE id = {$intApiModule}");
T::eq('module inactif invisible', 404, api_test_get('/modules/'.$intApiModule.'/pages', $arrKey['token'])['code']);
db()->query("UPDATE ploopi_module SET active = 1 WHERE id = {$intApiModule}");
db()->query('UPDATE ploopi_api_key SET window_start = '.time().', window_count = 120 WHERE id = '.$arrKey['id']);
$arrRep = api_test_get('/me', $arrKey['token']);
T::eq('limite atomique appliquée', 429, $arrRep['code']);
T::contient('délai de reprise', 'Retry-After: 60', $arrRep['headers']);
db()->query('UPDATE ploopi_api_key SET window_start = 0, window_count = 0, expires_at = 1 WHERE id = '.$arrKey['id']);
T::eq('clé expirée refusée', 401, api_test_get('/me', $arrKey['token'])['code']);
db()->query('UPDATE ploopi_api_key SET expires_at = '.(time()+86400).' WHERE id = '.$arrKey['id']);
ploopi\api_key::revoke($arrKey['id']);
T::eq('clé révoquée refusée', 401, api_test_get('/me', $arrKey['token'])['code']);
$arrStats = db()->query('SELECT request_count, failure_count FROM ploopi_api_key WHERE id = '.$arrKey['id'])->fetch_assoc();
T::vrai('appels comptés', (int) $arrStats['request_count'] > 10);
T::vrai('erreurs comptées', (int) $arrStats['failure_count'] > 5);
T::eq('secret absent des journaux', 0, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_api_request WHERE route LIKE '%".db()->real_escape_string($arrKey['token'])."%'")->fetch_assoc()['n']);
T::eq('429 compté mais absent du journal', 0, (int) db()->query('SELECT COUNT(*) AS n FROM ploopi_api_request WHERE status = 429 AND id_key = '.$arrKey['id'])->fetch_assoc()['n']);

T::section('Purge par le cron et métabase');
db()->query('INSERT INTO ploopi_api_request (id_key, route, status, duration_ms, created_at) VALUES ('.$arrKey['id'].", '/me', 200, 1, 1), (".$arrKey['id'].", '/me', 200, 1, ".time().')');
exec('php '.escapeshellarg(dirname(__DIR__).'/cron').' 2>&1', $arrCronOut, $intCronCode);
T::eq('cron exécuté', 0, $intCronCode);
T::eq('journal de plus de 30 jours purgé', 0, (int) db()->query('SELECT COUNT(*) AS n FROM ploopi_api_request WHERE created_at < '.(time() - 30 * 86400).' AND id_key = '.$arrKey['id'])->fetch_assoc()['n']);
T::vrai('journal récent conservé', (int) db()->query('SELECT COUNT(*) AS n FROM ploopi_api_request WHERE id_key = '.$arrKey['id'])->fetch_assoc()['n'] > 0);
T::eq('ploopi_log indexé sur ts', 1, (int) db()->query("SELECT COUNT(*) AS n FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ploopi_log' AND INDEX_NAME = 'ts'")->fetch_assoc()['n']);
$arrSansMb = array_column(db()->query("SELECT t.TABLE_NAME FROM information_schema.TABLES t LEFT JOIN ploopi_mb_table m ON m.name = t.TABLE_NAME AND m.id_module_type = 1 WHERE t.TABLE_SCHEMA = DATABASE() AND t.TABLE_NAME LIKE 'ploopi\\_%' AND t.TABLE_NAME NOT LIKE 'ploopi\\_mod\\_%' AND t.TABLE_NAME NOT LIKE 'ploopi\\_tests\\_%' AND m.name IS NULL")->fetch_all(MYSQLI_ASSOC), 'TABLE_NAME');
T::eq('toutes les tables du socle dans la métabase', array(), $arrSansMb);
T::eq('aucun champ secret dans la métabase', 0, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_mb_field WHERE (tablename, name) IN (('ploopi_user', 'password'), ('ploopi_session', 'data'), ('ploopi_session', 'id'), ('ploopi_serializedvar', 'data'), ('ploopi_connecteduser', 'sid'), ('ploopi_captcha', 'code'), ('ploopi_confirmation_code', 'code'), ('ploopi_api_key', 'token_hash'))")->fetch_assoc()['n']);
$objMbXml = simplexml_load_file(dirname(__DIR__).'/install/system/mb.xml');
T::eq('mb.xml et ploopi.sql décrivent les mêmes champs', count($objMbXml->ploopi_mb_field->row), (int) db()->query('SELECT COUNT(*) AS n FROM ploopi_mb_field WHERE id_module_type = 1')->fetch_assoc()['n']);

T::section('Tableau de bord et documentation intégrée');
H::reset();
H::connexion();
// Environnement (et jeton) de la session courante, à refaire après chaque connexion
$fnApiQuery = function ()
{
    $arrEnv = explode('/', H::env());
    $arrEnv[0] = _PLOOPI_MENU_WORKSPACES;
    $arrEnv[1] = 2;
    $arrEnv[2] = 1;
    $arrEnv[3] = 'admin';
    return '/admin.php?system_level=system&sysToolbarItem=api&ploopi_env='.urlencode(implode('/', $arrEnv));
};
$strQuery = $fnApiQuery();
$arrRep = H::req($strQuery);
$arrRep['body'] = html_entity_decode($arrRep['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
T::eq('tableau de bord accessible à l’admin', 200, $arrRep['code']);
T::contient('tableau de bord rendu', 'Clés d’accès', $arrRep['body']);
T::contient('statistiques rendues', 'Appels authentifiés', $arrRep['body']);
T::contientpas('le secret ne réapparaît pas', $arrKey['token'], $arrRep['body']);
$arrRep = H::req($strQuery.'&api_tab=docs');
$arrRep['body'] = html_entity_decode($arrRep['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
T::contient('documentation locale', 'Télécharger OpenAPI 3', $arrRep['body']);
T::contient('documentation du module', 'GET /modules/{module_id}/pages', $arrRep['body']);
$arrRep = H::req($strQuery.'&api_tab=docs&api_export=1');
T::eq('export OpenAPI JSON depuis le système', '3.0.3', json_decode($arrRep['body'], true)['openapi'] ?? '');

// Gabarit historique : blocs et tableaux du skin, sans composant de ploopi4
$strApiTemplate = (string) db()->query('SELECT template FROM ploopi_workspace WHERE id = 2')->fetch_row()[0];
db()->query("UPDATE ploopi_workspace SET template = 'ploopi3' WHERE id = 2");
H::reset();
H::connexion();
$strQuery = $fnApiQuery();
$arrRep = H::req($strQuery);
$arrRep['body'] = html_entity_decode($arrRep['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
T::eq('ploopi3 : tableau de bord accessible', 200, $arrRep['code']);
T::contient('ploopi3 : clés listées', 'Clés d’accès', $arrRep['body']);
T::contient('ploopi3 : formulaire de création', 'name="api_action" value="create"', $arrRep['body']);
T::contientpas('ploopi3 : pas de composant ploopi4', 'class="p4-stats', $arrRep['body']);
$arrRep = H::req($strQuery.'&api_tab=docs');
$arrRep['body'] = html_entity_decode($arrRep['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
T::contient('ploopi3 : documentation', 'GET /modules/{module_id}/pages', $arrRep['body']);
db()->query("UPDATE ploopi_workspace SET template = '".db()->real_escape_string($strApiTemplate)."' WHERE id = 2");
H::reset();
H::connexion();
$strQuery = $fnApiQuery();

T::section('Création et révocation par le formulaire système');
$arrRep = H::req($strQuery);
preg_match('@<form action="([^"]+)" method="post"@', $arrRep['body'], $arrForm);
$strFormUrl = isset($arrForm[1]) ? html_entity_decode($arrForm[1], ENT_QUOTES, 'UTF-8') : '';
T::vrai('le formulaire de création est disponible', $strFormUrl !== '');
if ($strFormUrl !== '')
{
    $arrRep = H::req('/'.ltrim($strFormUrl, '/'), http_build_query(array('api_action' => 'create', 'api_label' => 'Clé formulaire test', 'api_user' => $intApiUser, 'api_workspace' => 2, 'api_days' => 30, 'api_scopes' => array('core:read'))));
    $arrNewKey = db()->query("SELECT id FROM ploopi_api_key WHERE label = 'Clé formulaire test' ORDER BY id DESC LIMIT 1")->fetch_assoc();
    if ($arrNewKey) $arrApiKeyIds[] = (int) $arrNewKey['id'];
    preg_match('/plp_[a-f0-9]{64}/', $arrRep['body'], $arrSecret);
    T::vrai('la création affiche le secret une seule fois', !empty($arrSecret[0]));
    if (!empty($arrSecret[0]))
    {
        T::eq('la clé créée par l’écran fonctionne', 200, api_test_get('/me', $arrSecret[0])['code']);
        $arrRep = H::req($strQuery);
        T::contientpas('un rechargement ne montre plus le secret', $arrSecret[0], $arrRep['body']);
        $arrRep = H::req('/'.ltrim($strFormUrl, '/'), http_build_query(array('api_action' => 'revoke', 'api_key_id' => $arrNewKey['id'])));
        T::eq('révocation par le tableau de bord', 401, api_test_get('/me', $arrSecret[0])['code']);
    }
}
$intBefore = (int) db()->query('SELECT COUNT(*) AS n FROM ploopi_api_key')->fetch_assoc()['n'];
H::req('/admin.php?system_level=system&sysToolbarItem=api', http_build_query(array('api_action' => 'create', 'api_label' => 'Sans jeton', 'api_user' => $intApiUser, 'api_workspace' => 2, 'api_scopes' => array('core:read'))));
T::eq('aucune création sans jeton CSRF', $intBefore, (int) db()->query('SELECT COUNT(*) AS n FROM ploopi_api_key')->fetch_assoc()['n']);

T::eq('cookie admin seul ne suffit pas à l’API', 401, api_test_get('/me')['code']);
H::reset();
H::req('/admin.php', 'ploopi_login=api_fixture&ploopi_password='.urlencode('Api!Test2026'));
$strOrdinaryEnv = H::env();
$arrOrdinaryEnv = explode('/', $strOrdinaryEnv);
$arrOrdinaryEnv[0] = _PLOOPI_MENU_WORKSPACES;
$arrOrdinaryEnv[1] = 2;
$arrOrdinaryEnv[2] = 1;
$arrOrdinaryEnv[3] = 'admin';
$arrRep = H::req('/admin.php?system_level=system&sysToolbarItem=api&ploopi_env='.urlencode(implode('/', $arrOrdinaryEnv)));
T::contientpas('utilisateur ordinaire sans tableau de bord système', 'name="api_action"', $arrRep['body']);


exit(T::bilan('45_innodb_api'));
