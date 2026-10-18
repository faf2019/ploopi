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

namespace ploopi\system;

use ploopi;

/**
 * Démonstration : installe webedit et publie un petit site fictif, puis installe les
 * modules annuaire, agenda, réservation, actualités et rapports, garnis de données fictives.
 *
 * Proposé par Système › Modules sur une installation vierge seulement (aucun module
 * installé en dehors de system) : il ne peut donc rien écraser. Il installe le module
 * webedit par la procédure habituelle (ploopi\system\moduleinstaller), en crée une
 * instance dans le premier espace de travail, y publie rubriques et articles par les
 * classes du module (brouillon puis publication, indexation comprise), et ouvre le
 * frontoffice de cet espace. Les autres modules (self::MODULES) suivent la même
 * procédure ; leurs données sont datées de la semaine courante, pour que l'agenda et
 * les réservations s'affichent dès l'ouverture.
 *
 * @package system
 * @subpackage demo
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class demo
{
    /**
     * Module installé par la démonstration
     */
    const MODULE = 'webedit';

    /**
     * Modules installés ensuite, s'ils sont livrés, avec le libellé de leur instance et des
     * données fictives (méthodes data_<module>). dbreport vient après directory, dont il
     * interroge les contacts.
     */
    const MODULES = array(
        'directory' => 'Annuaire',
        'planning' => 'Agenda',
        'booking' => 'Réservation de salles',
        'news2' => 'Actualités',
        'dbreport' => 'Rapports',
    );

    /**
     * Espace de travail qui recevra le site : le premier espace hors system.
     *
     * @return int identifiant, 0 si aucun
     */
    public static function getworkspace()
    {
        $db = ploopi\db::get();
        $rs = $db->query('SELECT id FROM ploopi_workspace WHERE system = 0 ORDER BY depth, id LIMIT 1');
        $row = $db->fetchrow($rs);

        return $row ? (int) $row['id'] : 0;
    }

    /**
     * La démonstration ne se propose que sur une installation vierge : aucun module
     * installé en dehors de system, le paquet webedit présent et un espace pour l'accueillir.
     *
     * @return boolean
     */
    public static function isavailable()
    {
        $db = ploopi\db::get();
        $rs = $db->query("SELECT COUNT(*) AS nb FROM ploopi_module_type WHERE label <> 'system'");
        $row = $db->fetchrow($rs);

        return
            $row && (int) $row['nb'] === 0
            && file_exists('./install/'.self::MODULE.'/description.xml')
            && self::getworkspace() > 0;
    }

    /**
     * Génère le site de démonstration.
     *
     * @param array $rapport compte rendu, une ligne par opération (operation, detail, res)
     * @return int identifiant de l'espace qui porte le site, 0 en cas d'échec
     */
    public static function generate(&$rapport = array())
    {
        $rapport = array();

        if (!self::isavailable())
        {
            $rapport[] = array('operation' => 'Contrôle', 'detail' => 'La démonstration ne se génère que sur une installation sans autre module que system.', 'res' => false);
            return 0;
        }

        $intWorkspace = self::getworkspace();

        /**
         * 1. Installation du module
         */
        $arrInstall = array();
        $intModuleType = moduleinstaller::install(self::MODULE, $arrInstall);
        foreach ($arrInstall as $arrLigne) $rapport[] = $arrLigne;
        if ($intModuleType <= 0) return 0;

        /**
         * 2. Instance dans l'espace (crée la rubrique racine : include/create.php)
         */
        $intModule = self::instance($intModuleType, $intWorkspace, 'Site web', $rapport);

        /**
         * 3. Contenus
         */
        ploopi\module::init(self::MODULE, false, false, false);
        include_once './modules/webedit/class_heading.php';
        include_once './modules/webedit/class_article.php';
        include_once './modules/webedit/class_article_backup.php';

        $db = ploopi\db::get();
        $rs = $db->query("SELECT id FROM ploopi_mod_webedit_heading WHERE id_module = {$intModule} AND id_heading = 0 ORDER BY position LIMIT 1");
        $row = $db->fetchrow($rs);
        $intRacine = $row ? (int) $row['id'] : self::heading($intModule, $intWorkspace, 'Racine', '', 0, 1);

        $intNbRubriques = 0;
        $intNbArticles = 0;

        foreach (self::content() as $arrRacine)
        {
            $intRoot = ($arrRacine['position'] == 1) ? $intRacine : self::heading($intModule, $intWorkspace, $arrRacine['label'], '', 0, $arrRacine['position']);
            if ($arrRacine['position'] == 1) $db->query("UPDATE ploopi_mod_webedit_heading SET label = '".$db->addslashes($arrRacine['label'])."', id_workspace = {$intWorkspace} WHERE id = {$intRoot}");

            foreach ($arrRacine['articles'] as $intPos => $arrArticle) $intNbArticles += self::article($intModule, $intWorkspace, $intRoot, $arrArticle, $intPos + 1);

            foreach ($arrRacine['headings'] as $intPos => $arrRubrique)
            {
                $intRubrique = self::heading($intModule, $intWorkspace, $arrRubrique['label'], $arrRubrique['description'], $intRoot, $intPos + 1);
                $intNbRubriques++;
                foreach ($arrRubrique['articles'] as $intPosA => $arrArticle) $intNbArticles += self::article($intModule, $intWorkspace, $intRubrique, $arrArticle, $intPosA + 1);

                foreach ($arrRubrique['headings'] ?? array() as $intPosS => $arrSous)
                {
                    $intSous = self::heading($intModule, $intWorkspace, $arrSous['label'], $arrSous['description'], $intRubrique, $intPosS + 1);
                    $intNbRubriques++;
                    foreach ($arrSous['articles'] as $intPosA => $arrArticle) $intNbArticles += self::article($intModule, $intWorkspace, $intSous, $arrArticle, $intPosA + 1);
                }
            }
        }

        // Bloc de la colonne latérale du gabarit : article hors rubrique nommé bloc1
        $intNbArticles += self::article($intModule, $intWorkspace, 0, array('title' => 'bloc1', 'content' =>
            '<h3>Un projet ?</h3><p>Nos consultants vous répondent sous 48 heures.</p><p><a href="mailto:contact@exemple.fr">contact@exemple.fr</a><br>01 23 45 67 89</p>'), 1);

        $rapport[] = array('operation' => 'Contenus', 'detail' => "{$intNbRubriques} rubriques et {$intNbArticles} articles publiés.", 'res' => true);

        /**
         * 4. Frontoffice de l'espace
         */
        $objWorkspace = new ploopi\workspace();
        $objWorkspace->open($intWorkspace);
        $objWorkspace->fields['frontoffice'] = 1;
        if (trim((string) $objWorkspace->fields['frontoffice_domainlist']) === '') $objWorkspace->fields['frontoffice_domainlist'] = '*';
        $objWorkspace->fields['title'] = 'Nova Conseil';
        $objWorkspace->fields['meta_description'] = 'Nous accompagnons les collectivités et les entreprises dans leur transformation numérique : stratégie, conception de services et pilotage de projets.';
        $objWorkspace->fields['meta_copyright'] = 'Nova Conseil';
        $objWorkspace->save();

        $rapport[] = array('operation' => 'Site public', 'detail' => "Frontoffice activé pour l'espace n° {$intWorkspace} (gabarit « corporate »).", 'res' => true);

        /**
         * 5. Autres modules, avec des données fictives : seuls ceux dont le paquet est livré
         */
        $intUser = (int) $_SESSION['ploopi']['userid'];
        $arrTypes = array();
        foreach (self::MODULES as $strModule => $strLabel)
        {
            if (!file_exists("./install/{$strModule}/description.xml")) continue;

            $arrInstall = array();
            $intType = moduleinstaller::install($strModule, $arrInstall);
            if ($intType <= 0)
            {
                foreach ($arrInstall as $arrLigne) if (!$arrLigne['res']) $rapport[] = $arrLigne;
                $rapport[] = array('operation' => "Module {$strModule}", 'detail' => 'Installation impossible.', 'res' => false);
                continue;
            }
            $arrTypes[$strModule] = $intType;

            $intInstance = self::instance($intType, $intWorkspace, $strLabel, $rapport);
            $strDetail = self::{'data_'.$strModule}($intInstance, $intWorkspace, $intUser, $arrTypes);
            $rapport[] = array('operation' => "Données « {$strLabel} »", 'detail' => $strDetail, 'res' => true);
        }

        ploopi\user_action_log::record(_SYSTEM_ACTION_INSTALLMODULE, 'demo');

        return $intWorkspace;
    }

    /**
     * Crée une instance du module dans l'espace, active, visible et publique.
     *
     * @param int $intModuleType type de module
     * @param int $intWorkspace espace
     * @param string $strLabel libellé de l'instance
     * @param array $rapport compte rendu, complété
     * @return int identifiant de l'instance
     */
    private static function instance($intModuleType, $intWorkspace, $strLabel, &$rapport)
    {
        $objModuleType = new ploopi\module_type();
        $objModuleType->open($intModuleType);
        $objModule = $objModuleType->createinstance($intWorkspace);
        $objModule->fields['label'] = $strLabel;
        $objModule->fields['active'] = 1;
        $objModule->fields['visible'] = 1;
        $objModule->fields['public'] = 1;
        $intModule = $objModule->save();

        $objModuleWorkspace = new ploopi\module_workspace();
        $objModuleWorkspace->fields['id_module'] = $intModule;
        $objModuleWorkspace->fields['id_workspace'] = $intWorkspace;
        $objModuleWorkspace->save();

        ploopi\user_action_log::record(_SYSTEM_ACTION_USEMODULE, $objModuleType->fields['label']);
        $rapport[] = array('operation' => 'Instance du module', 'detail' => "Instance « {$strLabel} » créée dans l'espace n° {$intWorkspace}.", 'res' => true);

        return (int) $intModule;
    }

    /**
     * Insère un enregistrement : valeurs par défaut du schéma (init_description), puis
     * les champs donnés.
     *
     * @param string $strTable table
     * @param array $arrFields champs
     * @return int clé auto-incrémentée, 0 sans clé « id »
     */
    private static function row($strTable, $arrFields)
    {
        $objRecord = new ploopi\data_object($strTable);
        $objRecord->init_description();
        foreach ($arrFields as $strField => $mixValue) $objRecord->fields[$strField] = $mixValue;

        return (int) $objRecord->save();
    }

    /**
     * Horodatage YmdHis d'un jour de la semaine courante (0 : lundi).
     *
     * @param int $intDay jour, décalage depuis lundi
     * @param float $fltHour heure (9.5 : 9 h 30)
     * @return string
     */
    private static function weekday($intDay, $fltHour)
    {
        return date('Ymd', strtotime("monday this week +{$intDay} day")).sprintf('%02d%02d00', floor($fltHour), ($fltHour - floor($fltHour)) * 60);
    }

    /**
     * Annuaire : un organigramme de trois services et leurs contacts.
     *
     * @return string compte rendu
     */
    private static function data_directory($intModule, $intWorkspace, $intUser, $arrTypes)
    {
        $arrServices = array(
            'Direction' => array(
                array('Mme', 'Lambert', 'Claire', 'Directrice générale', '01 23 45 67 01'),
                array('M.', 'Roche', 'Antoine', 'Directeur administratif et financier', '01 23 45 67 02'),
            ),
            'Conseil' => array(
                array('Mme', 'Moreau', 'Inès', 'Consultante senior', '01 23 45 67 11'),
                array('M.', 'Garnier', 'Hugo', 'Consultant', '01 23 45 67 12'),
                array('Mme', 'Faure', 'Léa', 'Consultante', '01 23 45 67 13'),
                array('M.', 'Chevalier', 'Nathan', 'Chef de projet', '01 23 45 67 14'),
            ),
            'Études et développement' => array(
                array('M.', 'Bertrand', 'Lucas', 'Responsable technique', '01 23 45 67 21'),
                array('Mme', 'Girard', 'Manon', 'Développeuse', '01 23 45 67 22'),
                array('M.', 'Bonnet', 'Théo', 'Développeur', '01 23 45 67 23'),
            ),
        );

        // L'annuaire s'ouvre sur les favoris : une liste « Équipe projet » de l'utilisateur
        $intList = self::row('ploopi_mod_directory_list', array('label' => 'Équipe projet', 'id_user' => $intUser, 'id_workspace' => $intWorkspace, 'id_module' => $intModule));

        $intNb = 0;
        $intPosition = 0;
        foreach ($arrServices as $strService => $arrContacts)
        {
            $intHeading = self::row('ploopi_mod_directory_heading', array(
                'label' => $strService, 'position' => ++$intPosition, 'phone' => '01 23 45 67 00',
                'address' => '12 rue des Lilas', 'postalcode' => '75011', 'city' => 'Paris', 'country' => 'France',
                'id_heading' => 0, 'id_user' => $intUser, 'id_workspace' => $intWorkspace, 'id_module' => $intModule,
            ));

            foreach ($arrContacts as $intPos => $arrContact)
            {
                $intContact = self::row('ploopi_mod_directory_contact', array(
                    'civility' => $arrContact[0], 'lastname' => $arrContact[1], 'firstname' => $arrContact[2],
                    'service' => $strService, 'function' => $arrContact[3], 'phone' => $arrContact[4],
                    'email' => strtolower(ploopi\str::convertaccents($arrContact[2].'.'.$arrContact[1])).'@exemple.fr',
                    'address' => '12 rue des Lilas', 'postalcode' => '75011', 'city' => 'Paris', 'country' => 'France',
                    'building' => 'A', 'floor' => (string) $intPosition, 'office' => sprintf('%d%02d', $intPosition, $intPos + 1),
                    'id_heading' => $intHeading, 'position' => $intPos + 1,
                    'id_user' => $intUser, 'id_workspace' => $intWorkspace, 'id_module' => $intModule,
                ));
                if ($strService == 'Conseil') ploopi\db::get()->query("INSERT INTO ploopi_mod_directory_favorites (id_contact, id_user, id_ploopi_user, id_list) VALUES ({$intContact}, {$intUser}, 0, {$intList})");
                $intNb++;
            }
        }

        return count($arrServices)." services et {$intNb} contacts partagés, dont quatre en favoris.";
    }

    /**
     * Agenda : les rendez-vous de la semaine courante, attribués à l'utilisateur connecté.
     *
     * @return string compte rendu
     */
    private static function data_planning($intModule, $intWorkspace, $intUser, $arrTypes)
    {
        $arrEvents = array(
            array('Point d\'équipe hebdomadaire', 0, 9, 10),
            array('Atelier de cadrage – Ville de Lyon', 1, 10, 12.5),
            array('Déjeuner client', 1, 12.5, 14),
            array('Revue de projet', 2, 14, 15.5),
            array('Formation accessibilité', 2, 14.5, 17),
            array('Comité de pilotage', 3, 9.5, 11),
            array('Rédaction de la proposition', 3, 13, 17),
            array('Démonstration du prototype', 4, 10, 11.5),
        );

        foreach ($arrEvents as $arrEvent)
        {
            $intEvent = self::row('ploopi_mod_planning_event', array(
                'object' => $arrEvent[0], 'timestp_request' => ploopi\date::createtimestamp(),
                'id_user' => $intUser, 'id_workspace' => $intWorkspace, 'id_module' => $intModule,
            ));
            $intDetail = self::row('ploopi_mod_planning_event_detail', array(
                'timestp_begin' => self::weekday($arrEvent[1], $arrEvent[2]), 'timestp_end' => self::weekday($arrEvent[1], $arrEvent[3]),
                'validated' => 1, 'id_event' => $intEvent,
            ));
            ploopi\db::get()->query("INSERT INTO ploopi_mod_planning_event_detail_resource (id_event_detail, id_resource, type_resource, id_event) VALUES ({$intDetail}, {$intUser}, 'user', {$intEvent})");
        }

        return count($arrEvents).' rendez-vous cette semaine.';
    }

    /**
     * Réservation : trois salles et un véhicule, leurs équipements, et des réservations
     * de la semaine courante, validées ou en attente.
     *
     * @return string compte rendu
     */
    private static function data_booking($intModule, $intWorkspace, $intUser, $arrTypes)
    {
        $db = ploopi\db::get();
        $strNow = ploopi\date::createtimestamp();
        $arrUwm = array('id_user' => $intUser, 'id_workspace' => $intWorkspace, 'id_module' => $intModule);

        $arrResources = array(
            'Salles' => array(
                'Salle Lumière' => array('#3B82F6', array('Vidéoprojecteur', 'Visioconférence')),
                'Salle Curie' => array('#10B981', array('Écran tactile')),
                'Salle Eiffel' => array('#F59E0B', array()),
            ),
            'Véhicules' => array(
                'Utilitaire électrique' => array('#8B5CF6', array()),
            ),
        );

        $arrIds = array();
        foreach ($arrResources as $strType => $arrList)
        {
            $intType = self::row('ploopi_mod_booking_resourcetype', array('name' => $strType, 'active' => 1) + $arrUwm);
            foreach ($arrList as $strName => $arrResource)
            {
                $intResource = self::row('ploopi_mod_booking_resource', array(
                    'name' => $strName, 'color' => $arrResource[0], 'active' => 1, 'id_resourcetype' => $intType,
                    'timestp_create' => $strNow, 'timestp_modify' => $strNow,
                ) + $arrUwm);
                $db->query("INSERT INTO ploopi_mod_booking_resource_workspace (id_resource, id_workspace) VALUES ({$intResource}, {$intWorkspace})");
                foreach ($arrResource[1] as $strSub) self::row('ploopi_mod_booking_subresource', array(
                    'name' => $strSub, 'active' => 1, 'id_resource' => $intResource, 'timestp_create' => $strNow, 'timestp_modify' => $strNow,
                ) + $arrUwm);
                $arrIds[$strName] = $intResource;
            }
        }

        $arrEvents = array(
            array('Salle Lumière', 'Comité de pilotage', 3, 9.5, 11, 1),
            array('Salle Lumière', 'Atelier de cadrage', 1, 10, 12.5, 1),
            array('Salle Curie', 'Entretien de recrutement', 0, 14, 15, 1),
            array('Salle Curie', 'Revue de projet', 2, 14, 15.5, 1),
            array('Salle Eiffel', 'Formation accessibilité', 2, 14.5, 17, 0),
            array('Salle Eiffel', 'Démonstration du prototype', 4, 10, 11.5, 0),
            array('Utilitaire électrique', 'Livraison du matériel', 3, 8, 12, 1),
        );

        foreach ($arrEvents as $arrEvent)
        {
            $intEvent = self::row('ploopi_mod_booking_event', array(
                'id_resource' => $arrIds[$arrEvent[0]], 'object' => $arrEvent[1], 'managed' => $arrEvent[5], 'timestp_request' => $strNow,
            ) + $arrUwm);
            self::row('ploopi_mod_booking_event_detail', array(
                'timestp_begin' => self::weekday($arrEvent[2], $arrEvent[3]), 'timestp_end' => self::weekday($arrEvent[2], $arrEvent[4]),
                'validated' => $arrEvent[5], 'id_event' => $intEvent,
            ));
        }

        // Planning ouvert sur la semaine, toutes les ressources affichées, une colonne par jour
        foreach (array('booking_default_all_checked' => '1', 'booking_default_display_type' => 'week', 'booking_default_channels' => '0') as $strName => $strValue)
            $db->query("UPDATE ploopi_param_default SET value = '{$strValue}' WHERE id_module = {$intModule} AND name = '{$strName}'");

        return count($arrIds).' ressources et '.count($arrEvents).' réservations cette semaine, dont deux à valider.';
    }

    /**
     * Actualités : deux catégories et quelques nouvelles publiées.
     *
     * @return string compte rendu
     */
    private static function data_news2($intModule, $intWorkspace, $intUser, $arrTypes)
    {
        $arrUwm = array('id_user' => $intUser, 'id_workspace' => $intWorkspace, 'id_module' => $intModule);
        $strNow = date('Y-m-d H:i:s');

        $arrNews = array(
            'Vie du cabinet' => array(
                array('Nova Conseil s\'installe dans de nouveaux locaux', 'Plus de place pour accueillir nos clients et nos ateliers.', '<p>Après dix ans rue de la Roquette, l\'équipe emménage au 12 rue des Lilas : trois salles de réunion, un espace d\'ateliers et un studio de visioconférence.</p>', 1),
                array('Bienvenue à Manon et Théo', 'Deux développeurs rejoignent l\'équipe Études.', '<p>Ils renforcent l\'équipe Études et développement sur les projets de services publics numériques.</p>', 0),
            ),
            'Projets' => array(
                array('Le portail de la Ville de Lyon mis en ligne', 'Un an de travail avec les services municipaux.', '<p>Le nouveau portail, conforme au RGAA, regroupe les démarches en ligne des habitants.</p>', 1),
                array('Atelier accessibilité ouvert aux partenaires', 'Inscriptions ouvertes pour la session de novembre.', '<p>Une journée pour découvrir les bonnes pratiques d\'accessibilité numérique.</p>', 0),
            ),
        );

        $intNb = 0;
        foreach ($arrNews as $strCat => $arrEntries)
        {
            $intCat = self::row('ploopi_mod_news2_cat', array('title' => $strCat, 'description' => '', 'date_create' => $strNow, 'date_modify' => $strNow) + $arrUwm);
            foreach ($arrEntries as $arrEntry)
            {
                self::row('ploopi_mod_news2_entry', array(
                    'id_cat' => $intCat, 'title' => $arrEntry[0], 'resume' => $arrEntry[1], 'content' => $arrEntry[2],
                    'published' => 1, 'hot' => $arrEntry[3], 'date_publish' => date('YmdHis', strtotime('-'.(++$intNb * 3).' day')),
                ) + $arrUwm);
            }
        }

        return count($arrNews)." catégories et {$intNb} nouvelles publiées.";
    }

    /**
     * Rapports : deux requêtes sur les contacts de l'annuaire, dont une en graphique.
     *
     * @return string compte rendu
     */
    private static function data_dbreport($intModule, $intWorkspace, $intUser, $arrTypes)
    {
        if (empty($arrTypes['directory'])) return 'Aucune requête : l\'annuaire n\'est pas installé.';

        $db = ploopi\db::get();
        $intDirectory = (int) $arrTypes['directory'];
        $strTable = 'ploopi_mod_directory_contact';
        $arrUwm = array('id_user' => $intUser, 'id_workspace' => $intWorkspace, 'id_module' => $intModule, 'timestp_update' => ploopi\date::createtimestamp());

        $arrQueries = array(
            array('Liste des contacts', array(
                array('lastname', 'Nom', '', 'asc'), array('firstname', 'Prénom', '', ''), array('service', 'Service', '', ''),
                array('function', 'Fonction', '', ''), array('phone', 'Téléphone', '', ''),
            ), null),
            array('Contacts par service', array(
                array('service', 'Service', 'groupby', 'asc'), array('id', 'Contacts', 'count', ''),
            ), 'pie'),
        );

        foreach ($arrQueries as $arrQuery)
        {
            $intQuery = self::row('ploopi_mod_dbreport_query', array('label' => $arrQuery[0]) + $arrUwm);
            self::row('ploopi_mod_dbreport_querytable', array('tablename' => $strTable, 'id_module_type' => $intDirectory, 'id_query' => $intQuery));
            $db->query("INSERT INTO ploopi_mod_dbreport_query_module_type (id_query, id_module_type) VALUES ({$intQuery}, {$intDirectory})");

            $arrFieldIds = array();
            foreach ($arrQuery[1] as $intPos => $arrField)
            {
                $arrFieldIds[] = self::row('ploopi_mod_dbreport_queryfield', array(
                    'tablename' => $strTable, 'id_module_type' => $intDirectory, 'fieldname' => $arrField[0], 'label' => $arrField[1],
                    'operation' => $arrField[2], 'sort' => $arrField[3], 'visible' => 1, 'position' => $intPos + 1, 'id_query' => $intQuery,
                ));
            }

            if ($arrQuery[2]) $db->query("UPDATE ploopi_mod_dbreport_query SET chart = '{$arrQuery[2]}', chart_x = {$arrFieldIds[0]}, chart_val = {$arrFieldIds[1]}, chart_title = '".$db->addslashes($arrQuery[0])."' WHERE id = {$intQuery}");
        }

        return count($arrQueries).' requêtes sur l\'annuaire, dont une en graphique.';
    }

    /**
     * Crée une rubrique.
     *
     * @return int identifiant
     */
    private static function heading($intModule, $intWorkspace, $strLabel, $strDescription, $intParent, $intPosition)
    {
        $objParent = new \webedit_heading();
        $booParent = $intParent > 0 && $objParent->open($intParent);

        $objHeading = new \webedit_heading();
        $objHeading->init_description();
        $objHeading->fields['label'] = $strLabel;
        $objHeading->fields['description'] = $strDescription;
        $objHeading->fields['id_heading'] = $intParent;
        $objHeading->fields['parents'] = $booParent ? "{$objParent->fields['parents']};{$intParent}" : '0';
        $objHeading->fields['depth'] = $booParent ? $objParent->fields['depth'] + 1 : 1;
        $objHeading->fields['position'] = $intPosition;
        $objHeading->fields['visible'] = 1;
        $objHeading->fields['content_type'] = 'articles';
        $objHeading->fields['feed_enabled'] = 1;
        $objHeading->fields['subscription_enabled'] = 1;
        $objHeading->fields['id_module'] = $intModule;
        $objHeading->fields['id_workspace'] = $intWorkspace;
        $objHeading->fields['id_user'] = $_SESSION['ploopi']['userid'];

        return (int) $objHeading->save();
    }

    /**
     * Crée un article en brouillon puis le publie (indexation comprise).
     *
     * @return int 1
     */
    private static function article($intModule, $intWorkspace, $intHeading, $arrArticle, $intPosition)
    {
        $strPublie = ploopi\date::createtimestamp();

        $objDraft = new \webedit_article('draft');
        $objDraft->init_description();
        $objDraft->fields['title'] = $arrArticle['title'];
        $objDraft->fields['content'] = $arrArticle['content'];
        $objDraft->fields['author'] = 'Nova Conseil';
        $objDraft->fields['version'] = 1;
        $objDraft->fields['visible'] = 1;
        $objDraft->fields['status'] = 'edit';
        $objDraft->fields['timestp_published'] = $strPublie;
        $objDraft->fields['timestp_unpublished'] = 0;
        $objDraft->fields['lastupdate_timestp'] = $strPublie;
        $objDraft->fields['lastupdate_id_user'] = $_SESSION['ploopi']['userid'];
        $objDraft->fields['id_heading'] = $intHeading;
        $objDraft->fields['position'] = $intPosition;
        $objDraft->fields['id_module'] = $intModule;
        $objDraft->fields['id_workspace'] = $intWorkspace;
        $objDraft->fields['id_user'] = $_SESSION['ploopi']['userid'];
        $objDraft->save();
        $objDraft->publish();

        return 1;
    }

    /**
     * Contenu du site fictif : racines (1 : menu principal, 2 : pied de page), rubriques,
     * sous-rubriques et articles.
     *
     * @return array
     */
    private static function content()
    {
        return array(
            array(
                'position' => 1,
                'label' => 'Accueil',
                'articles' => array(
                    array('title' => 'Construisons des services publics qui simplifient la vie', 'content' =>
                        '<h2>Notre approche</h2>'
                        .'<p>Depuis quinze ans, Nova Conseil aide les organisations à concevoir des services numériques <strong>utiles, accessibles et durables</strong>. Nous partons toujours des usages réels : ceux des agents comme ceux des habitants.</p>'
                        .'<blockquote>« Un bon service public numérique est celui qu’on n’a pas besoin d’expliquer. »</blockquote>'
                        .'<h3>Ce que nous faisons</h3>'
                        .'<ul><li>Diagnostic et feuille de route numérique</li><li>Conception de services centrée sur les usagers</li><li>Pilotage de projets et accompagnement au changement</li></ul>'
                        .'<p>Chaque mission commence par une immersion sur le terrain et se termine par un transfert complet de compétences à vos équipes.</p>'),
                ),
                'headings' => array(
                    array(
                        'label' => 'Expertises',
                        'description' => 'Stratégie numérique, conception de services publics, accompagnement au changement.',
                        'articles' => array(
                            array('title' => 'Nos expertises', 'content' =>
                                '<p>Trois métiers complémentaires pour mener vos projets de bout en bout.</p>'
                                .'<table><thead><tr><th>Métier</th><th>Livrables</th><th>Durée type</th></tr></thead><tbody>'
                                .'<tr><td>Stratégie</td><td>Diagnostic, feuille de route</td><td>6 à 10 semaines</td></tr>'
                                .'<tr><td>Conception</td><td>Parcours, prototypes, tests</td><td>2 à 4 mois</td></tr>'
                                .'<tr><td>Pilotage</td><td>AMOA, conduite du changement</td><td>Durée du projet</td></tr>'
                                .'</tbody></table>'),
                            array('title' => 'Méthode', 'content' => '<p>Une méthode éprouvée en quatre temps : <strong>comprendre</strong>, <strong>imaginer</strong>, <strong>tester</strong>, <strong>déployer</strong>. Chaque étape se conclut par une décision partagée avec vos équipes.</p>'),
                        ),
                        'headings' => array(
                            array('label' => 'Stratégie', 'description' => 'Feuille de route et gouvernance', 'articles' => array(
                                array('title' => 'Stratégie numérique', 'content' => '<p>Diagnostic de l’existant, ateliers avec les directions, feuille de route priorisée et chiffrée.</p>'))),
                            array('label' => 'Conception de services', 'description' => 'Design de services et parcours usagers', 'articles' => array(
                                array('title' => 'Conception de services', 'content' => '<p>Entretiens, cartes de parcours, prototypes testés avec de vrais usagers avant tout développement.</p>'))),
                            array('label' => 'Pilotage de projets', 'description' => 'Assistance à maîtrise d’ouvrage', 'articles' => array(
                                array('title' => 'Pilotage de projets', 'content' => '<p>Rédaction des cahiers des charges, suivi des prestataires, recette et accompagnement au déploiement.</p>'))),
                        ),
                    ),
                    array(
                        'label' => 'Références',
                        'description' => 'Plus de 120 projets menés pour des collectivités, établissements publics et ETI.',
                        'articles' => array(
                            array('title' => 'Nos références', 'content' => '<p>Collectivités territoriales, établissements publics, entreprises de taille intermédiaire : quelques projets récents.</p><ul><li><strong>Ville de Fleurville</strong> — portail citoyen et démarches en ligne</li><li><strong>Communauté d’agglomération du Val</strong> — schéma directeur numérique</li><li><strong>Office de tourisme</strong> — refonte du site et de la billetterie</li></ul>'),
                        ),
                    ),
                    array(
                        'label' => 'Actualités',
                        'description' => 'Nos analyses, retours d’expérience et événements à venir.',
                        'articles' => array(
                            array('title' => 'Lancement du nouveau portail citoyen de Fleurville', 'content' => '<p>Après huit mois de travail avec les services municipaux, le nouveau portail est en ligne. Les démarches les plus fréquentes se font désormais en moins de trois minutes.</p>'),
                            array('title' => 'Accessibilité : ce qui change en 2026', 'content' => '<p>Retour sur les nouvelles obligations d’accessibilité numérique et sur la manière de les intégrer dès la conception.</p>'),
                        ),
                    ),
                    array(
                        'label' => 'Contact',
                        'description' => 'Parlons de votre projet : un premier échange sans engagement.',
                        'articles' => array(
                            array('title' => 'Nous contacter', 'content' => '<p>Nova Conseil<br>12 rue des Lilas<br>00000 Fleurville</p><p>Téléphone : 01 23 45 67 89<br>Courriel : <a href="mailto:contact@exemple.fr">contact@exemple.fr</a></p>'),
                        ),
                    ),
                ),
            ),
            array(
                'position' => 2,
                'label' => 'Pied de page',
                'articles' => array(),
                'headings' => array(
                    array('label' => 'Mentions légales', 'description' => '', 'articles' => array(
                        array('title' => 'Mentions légales', 'content' => '<p>Site de démonstration généré par Ploopi. Nova Conseil est une entreprise fictive ; les informations de ce site ne sont pas réelles.</p>'))),
                ),
            ),
        );
    }
}
