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
 * Système > API : émission/révocation, activité et documentation locale.
 * @package system
 * @subpackage api
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
use ploopi\system\ui;

// Blocs et tableaux partagés avec les gabarits historiques (system_open_bloc…)
if (!function_exists('system_open_bloc')) include_once './modules/system/include/functions.php';

if (!ploopi\acl::isadmin()) return;
header('Cache-Control: no-store');
$resApiVersion = ploopi\db::get()->query('SELECT version FROM ploopi_module_type WHERE id = 1');
$arrApiVersion = ploopi\db::get()->fetchrow($resApiVersion);
if (!$arrApiVersion || version_compare($arrApiVersion['version'], '2.1.0', '<'))
{
    echo system_pagehead(_SYSTEM_MENU_API, 'Mise à jour nécessaire');
    echo system_message('warning', 'Effectuez la mise à jour de la base dans Système → Mise à jour pour activer l’API.');
    return;
}
$strApiUrl = 'admin.php?system_level=system&sysToolbarItem=api';
$strApiTab = isset($_GET['api_tab']) && $_GET['api_tab'] === 'docs' ? 'docs' : 'dashboard';
$strApiError = '';
$strApiSecret = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_action']))
{
    if (!ploopi\loader::checktoken())
    {
        http_response_code(403);
        $strApiError = 'Jeton de sécurité invalide. Rechargez la page.';
    }
    else
    {
        try
        {
            switch ($_POST['api_action'])
            {
                case 'create':
                    if (!is_scalar($_POST['api_label'] ?? null) || !is_array($_POST['api_scopes'] ?? null)) throw new \InvalidArgumentException('Formulaire incomplet.');
                    foreach ($_POST['api_scopes'] as $strApiScope) if (!is_string($strApiScope)) throw new \InvalidArgumentException('Permission invalide.');
                    $arrApiCreated = ploopi\api_key::issue((int) ($_POST['api_user'] ?? 0), (int) ($_POST['api_workspace'] ?? 0), (string) $_POST['api_label'], $_POST['api_scopes'], (int) ($_POST['api_days'] ?? 90));
                    $strApiSecret = $arrApiCreated['token'];
                    ploopi\user_action_log::record(_SYSTEM_ACTION_PARAMMODULE, 'API : création clé '.$arrApiCreated['id']);
                break;
                case 'revoke':
                    ploopi\api_key::revoke((int) ($_POST['api_key_id'] ?? 0));
                    ploopi\user_action_log::record(_SYSTEM_ACTION_PARAMMODULE, 'API : révocation clé '.(int) ($_POST['api_key_id'] ?? 0));
                break;
                default: throw new \InvalidArgumentException('Action inconnue.');
            }
        }
        catch (\InvalidArgumentException $objApiError) { $strApiError = $objApiError->getMessage(); }
        catch (\Throwable $objApiError) { $strApiError = 'Opération impossible. Consultez le journal du serveur.'; error_log($objApiError->getMessage()); }
    }
}

if ($strApiTab === 'docs' && isset($_GET['api_export']))
{
    ploopi\buffer::clean();
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="ploopi-openapi.json"');
    ploopi\system::kill(json_encode(ploopi\api::openapi(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
}

// Rendu ploopi4 ou historique par les fonctions system_* (include/functions.php)
echo system_pagehead(_SYSTEM_MENU_API, 'Accès aux données de Ploopi et de ses modules en lecture seule');
echo system_tabs(array('dashboard' => array('Tableau de bord', $strApiUrl, 'activity'), 'docs' => array('Documentation', $strApiUrl.'&api_tab=docs', 'file-text')), $strApiTab, 'Administration de l’API');
if ($strApiError !== '') echo system_message('danger', ui::e($strApiError));
if ($strApiSecret !== '') echo system_message('warning', 'Copiez cette clé maintenant : elle ne sera plus affichée.<pre><code>'.ui::e($strApiSecret).'</code></pre>');

if ($strApiTab === 'docs')
{
    echo system_open_bloc('Utiliser l’API', 'code');
    ?>
    <div<?php echo system_bloc_padding(); ?>>
    <p>Envoyez votre clé dans l’en-tête <code>Authorization: Bearer VOTRE_CLE</code>, en HTTPS. Les cookies et les clés dans l’URL ne sont pas acceptés.</p>
    <p>Chaque clé est rattachée à un utilisateur, à un espace et à des permissions de lecture. Les droits, l’état du compte (désactivation, expiration) et le rattachement à l’espace sont revérifiés à chaque appel. Les données des modules restent limitées à cet espace, même lorsque leur interface offre une vue transverse.</p>
    <pre><code><?php echo ui::e('curl -H "Authorization: Bearer VOTRE_CLE" "'._PLOOPI_BASEPATH.'/api/v1/me"'); ?></code></pre>
    <p>Pour les collections de module : <code>?page=1&amp;per_page=25</code> (100 éléments maximum par page). La réponse contient <code>data</code> et <code>meta</code> : page, per_page, total. Le détail contient <code>data</code>. Les identifiants de wiki doivent être encodés dans l’URL.</p>
    <p>Exemple : <code>{"data": [], "meta": {"page": 1, "per_page": 25, "total": 0}}</code></p>
    <p>Limite : 120 requêtes par minute et par clé ; une réponse 429 indique le délai par <code>Retry-After</code>. Seule la méthode GET est acceptée ; aucune donnée métier n’est modifiée. Les appels authentifiés sont comptabilisés et leur statut est journalisé pendant 30 jours (purge par le cron), sans clé ni corps de requête ; les réponses 429 ne sont que comptées.</p>
    <p>Erreurs JSON : <code>{"error": {"code": "forbidden", "message": "…"}}</code>. Statuts : 400 paramètre invalide, 401 clé absente/invalide, 403 droits insuffisants, 404 ressource absente, 405 méthode refusée, 429 limite atteinte, 500 erreur serveur.</p>
    <p><?php echo system_button(array('label' => 'Télécharger OpenAPI 3', 'icon' => 'download', 'href' => ploopi\crypt::urlencode($strApiUrl.'&api_tab=docs&api_export=1'))); ?></p>
    </div>
    <?php
    echo system_close_bloc();
    foreach (ploopi\api::openapi()['paths'] as $strApiPath => $arrApiPath)
    {
        echo system_open_bloc('GET '.$strApiPath, 'code');
        echo '<div'.system_bloc_padding().'><p>'.ui::e($arrApiPath['get']['summary']).'</p><p>'.ui::e($arrApiPath['get']['description']).'</p>';
        foreach ($arrApiPath['get']['parameters'] as $arrApiParam) echo '<p><code>'.ui::e($arrApiParam['name']).'</code> — '.ui::e($arrApiParam['in']).(!empty($arrApiParam['required']) ? ' (obligatoire)' : ' (facultatif)').'</p>';
        if (isset($arrApiPath['get']['responses'][200]['content']['application/json']['schema']))
            echo '<details><summary>Schéma JSON de la réponse</summary><pre><code>'.ui::e(json_encode($arrApiPath['get']['responses'][200]['content']['application/json']['schema'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</code></pre></details>';
        echo '</div>'.system_close_bloc();
    }
    echo ui::script();
    return;
}

$objApiDb = ploopi\db::get();
$resApiStats = $objApiDb->query('SELECT COUNT(*) AS total, COALESCE(SUM(revoked_at = 0 AND expires_at > '.time().'), 0) AS active, COALESCE(SUM(request_count), 0) AS calls, COALESCE(SUM(failure_count), 0) AS failures FROM ploopi_api_key');
$arrApiStats = $objApiDb->fetchrow($resApiStats);
echo system_stats(array(array('Clés actives', $arrApiStats['active'], 'key'), array('Appels authentifiés', $arrApiStats['calls'], 'activity'), array('Appels en erreur', $arrApiStats['failures'], 'alert-triangle')));
echo system_open_bloc('Créer une clé', 'key');
?>
<form action="<?php echo ploopi\crypt::urlencode($strApiUrl); ?>" method="post"<?php echo ploopi\skin::get()->version() >= 4 ? ' class="p4-ui"' : system_bloc_padding(); ?>>
    <input type="hidden" name="api_action" value="create" />
    <p><label>Libellé <input class="<?php echo ploopi\skin::get()->version() >= 4 ? 'p4-input' : 'text'; ?>" name="api_label" maxlength="100" required /></label></p>
    <p><label>Utilisateur <select class="<?php echo ploopi\skin::get()->version() >= 4 ? 'p4-input' : 'select'; ?>" name="api_user" required>
        <?php
        $resApiUsers = $objApiDb->query('SELECT id, login, firstname, lastname FROM ploopi_user WHERE disabled = 0 ORDER BY login');
        while ($arrApiUser = $objApiDb->fetchrow($resApiUsers)) echo '<option value="'.(int) $arrApiUser['id'].'">'.ui::e($arrApiUser['login'].' — '.$arrApiUser['firstname'].' '.$arrApiUser['lastname']).'</option>';
        ?>
    </select></label></p>
    <p><label>Espace autorisé <select class="<?php echo ploopi\skin::get()->version() >= 4 ? 'p4-input' : 'select'; ?>" name="api_workspace" required>
        <?php
        $resApiWorkspaces = $objApiDb->query('SELECT id, label FROM ploopi_workspace WHERE system = 0 AND backoffice = 1 ORDER BY label');
        while ($arrApiWorkspace = $objApiDb->fetchrow($resApiWorkspaces)) echo '<option value="'.(int) $arrApiWorkspace['id'].'">'.ui::e($arrApiWorkspace['label']).'</option>';
        ?>
    </select></label></p>
    <p><label>Validité en jours <input class="<?php echo ploopi\skin::get()->version() >= 4 ? 'p4-input' : 'text'; ?>" name="api_days" type="number" min="1" max="365" value="90" required /></label></p>
    <fieldset><legend>Permissions</legend>
        <?php foreach (ploopi\api::scopes() as $strApiScope => $strApiLabel) { ?>
            <p><label><input type="checkbox" name="api_scopes[]" value="<?php echo ui::e($strApiScope); ?>" <?php if ($strApiScope === 'core:read') echo 'checked'; ?> /> <?php echo ui::e($strApiLabel); ?></label></p>
        <?php } ?>
    </fieldset>
    <p><?php echo system_button(array('label' => 'Créer la clé', 'type' => 'submit', 'class' => 'primary', 'icon' => 'key')); ?></p>
</form>
<?php
echo system_close_bloc();
echo system_open_bloc('Clés d’accès', 'key');
$resApiKeys = $objApiDb->query('SELECT k.id, k.label, k.prefix, k.scopes, k.expires_at, k.revoked_at, k.last_used_at, k.request_count, u.login, w.label AS workspace FROM ploopi_api_key k LEFT JOIN ploopi_user u ON u.id = k.id_user LEFT JOIN ploopi_workspace w ON w.id = k.id_workspace ORDER BY k.id DESC');
// Même déclaration pour les deux rendus : la clé en largeur libre, puis des colonnes à largeur
// fixe (groupe « right » : la première déclarée est la plus à droite)
$arrApiColumns = array('auto' => array('label' => array('label' => 'Clé')), 'right' => array());
foreach (array('action' => array('Action', 90), 'last' => array('Dernier appel', 120), 'calls' => array('Appels', 70), 'status' => array('État', 140), 'scopes' => array('Permissions', 160), 'owner' => array('Utilisateur / espace', 180)) as $strApiCol => $arrApiCol) $arrApiColumns['right'][$strApiCol] = array('label' => $arrApiCol[0], 'width' => $arrApiCol[1]);
$arrApiRows = array();
while ($arrApiKey = $objApiDb->fetchrow($resApiKeys))
{
    $strApiState = $arrApiKey['revoked_at'] ? 'Révoquée' : ($arrApiKey['expires_at'] <= time() ? 'Expirée' : 'Active');
    $strApiAction = '';
    if (!$arrApiKey['revoked_at']) $strApiAction = '<form method="post" action="'.ui::e(ploopi\crypt::urlencode($strApiUrl)).'"><input type="hidden" name="api_action" value="revoke" /><input type="hidden" name="api_key_id" value="'.(int) $arrApiKey['id'].'" />'.system_button(array('label' => 'Révoquer', 'type' => 'submit', 'class' => 'danger', 'confirm' => 'Révoquer immédiatement cette clé ?')).'</form>';
    $arrApiCells = array('label' => ui::e($arrApiKey['label']).'<br /><code>'.ui::e($arrApiKey['prefix']).'…</code>', 'owner' => ui::e($arrApiKey['login'].' / '.$arrApiKey['workspace']), 'scopes' => ui::e($arrApiKey['scopes']), 'status' => ui::e($strApiState.' — '.date('d/m/Y', $arrApiKey['expires_at'])), 'calls' => (int) $arrApiKey['request_count'], 'last' => $arrApiKey['last_used_at'] ? ui::e(date('d/m/Y H:i', $arrApiKey['last_used_at'])) : '—', 'action' => $strApiAction);
    $arrApiRow = array('values' => array());
    foreach ($arrApiCells as $strApiCol => $strApiValue) $arrApiRow['values'][$strApiCol] = array('label' => $strApiValue);
    $arrApiRows[] = $arrApiRow;
}
system_display_array($arrApiColumns, $arrApiRows, 'system_api_keys', array('filter' => 'Filtrer les clés', 'limit' => 20, 'empty' => 'Aucune clé API.'));
echo system_close_bloc();
echo system_open_bloc('Derniers appels authentifiés', 'activity');
$resApiRequests = $objApiDb->query('SELECT r.*, k.label FROM ploopi_api_request r LEFT JOIN ploopi_api_key k ON k.id = r.id_key ORDER BY r.id DESC LIMIT 50');
$arrApiRows = array();
$arrApiColumns = array('auto' => array('route' => array('label' => 'Route')), 'left' => array());
foreach (array('date' => array('Date', 140), 'key' => array('Clé', 160)) as $strApiCol => $arrApiCol) $arrApiColumns['left'][$strApiCol] = array('label' => $arrApiCol[0], 'width' => $arrApiCol[1]);
$arrApiColumns['right'] = array('duration' => array('label' => 'Durée', 'width' => 80), 'status' => array('label' => 'HTTP', 'width' => 60));
while ($arrApiRequest = $objApiDb->fetchrow($resApiRequests))
{
    $arrApiRow = array('values' => array());
    foreach (array('date' => date('d/m/Y H:i:s', $arrApiRequest['created_at']), 'key' => $arrApiRequest['label'], 'route' => $arrApiRequest['route'], 'status' => $arrApiRequest['status'], 'duration' => $arrApiRequest['duration_ms'].' ms') as $strApiCol => $strApiValue) $arrApiRow['values'][$strApiCol] = array('label' => ui::e($strApiValue));
    $arrApiRows[] = $arrApiRow;
}
system_display_array($arrApiColumns, $arrApiRows, 'system_api_requests', array('empty' => 'Aucun appel enregistré.'));
echo system_close_bloc();
echo ui::script();
