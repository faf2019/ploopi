<?php
/*
    Copyright (c) 2007-2018 Ovensia
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

namespace ploopi;

use ploopi;

/**
 * Gestion de la connexion à la base MySQL/MariaDB
 * Permet de se connecter, d'exécuter des requêtes, etc...
 *
 * @package ploopi
 * @subpackage database
 * @copyright Ovensia
 * @license GNU General var License (GPL)
 * @author Ovensia
 */

class db
{
    /**
     * Détermine si la connexion est permanente
     *
     * @var boolean
     */

    private $persistency = false;

    /**
     * Nom d'utilisateur pour la connexion à la BDD
     *
     * @var string
     */

    private $user = '';

    /**
     * Mot de passe pour la connexion à la BDD
     *
     * @var string
     */

    private $password = '';

    /**
     * Nom du serveur (hôte, ip) pour la connexion à la BDD
     *
     * @var string
     */

    private $server = '';

    /**
     * Port pour la connexion à la BDD
     *
     * @var string
     */

    private $port = '';

    /**
     * Nom de la base de données pour la connexion à la BDD
     *
     * @var string
     */

    private $database = '';

    /**
     * Objet mysqli
     *
     * @var mysqli
     */

    private $mysqli;

    /**
     * Pointeur sur le résultat de la dernière requête exécutée
     *
     * @var ressource
     */

    private $query_result;

    /**
     * Compteur de requêtes exécutées
     *
     * @var int
     */

    private $num_queries;

    /**
     * Temps d'exécution SQL global depuis le début du script (en ms)
     *
     * @var int
     */
    private $exectime_queries;

    /**
     * Timer d'exécution
     *
     * @var timer
     */

    private $db_timer = null;

    /**
     * Dernière erreur retournée
     *
     * @var string
     */

    private $last_error = '';

    /**
     * Dernier numéro d'erreur retourné
     *
     * @var int
     */

    private $last_errorno = '';

    /**
     * Dernière requête exécutée
     *
     * @var string
     */

    private $last_query = '';

    /**
     * Log des requêtes exécutées par l'instance
     *
     * @var array
     */

    private $arrLog = [];

    /**
     * Activation du log de requête ou non
     *
     * @var boolean
     */

    private $log = false;


    /**
     * Singleton de connexion à la base de donnée
     *
     * @var db
     */
    private static $_objDb = null;


    /**
     * Constructeur de la classe. Connexion à une base de données, sélection de la base.
     *
     * @param string $server adresse du serveur mysql
     * @param string $user nom utilisateur pour la connexion à mysql
     * @param string $password mot de passe utilisateur pour la connexion à mysql
     * @param string $database base à sélectionner
     * @param boolean $persistency true si connexion persistente, false sinon. Par défaut : false
     * @return mixed false si problème de connexion, id de connexion sinon
     */

    public function __construct($server, $user, $password = '', $database = '', $persistency = false, $log = false, $timeout = 1, $charset = 'utf8')
    {
        $this->persistency = $persistency;
        $this->user = $user;
        $this->password = $password;
        $server_detail = explode(':', $server);
        $this->server = $server_detail[0];
        $this->port = '3306';

        if (sizeof($server_detail) > 1) $this->port = $server_detail[1];

        $this->database = $database;
        $this->mysqli = null;
        $this->num_queries = 0;
        $this->exectime_queries = 0;
        $this->query_result = null;
        $this->log = $log;

        if ($this->log) $this->flush_log();

        if ($this->persistency) $this->server = 'p:'.$this->server;

        $this->timer_start();

        mysqli_report(MYSQLI_REPORT_OFF);
        
        $this->mysqli = mysqli_init();
        $this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
        @$this->mysqli->real_connect($this->server, $this->user, $this->password, $this->database, $this->port);

        $this->timer_stop();

        if ($this->mysqli->connect_errno) {
            trigger_error($this->mysqli->connect_error, E_USER_WARNING);
            $this->mysqli = null;
        }
        else {
            $this->mysqli->set_charset($charset);
        }
    }


    /**
     * Retourne le connecteur vers la base de données
     *
     * @return db instance du connecteur à la base de donnée
     */

    public static function get() {
        if (!is_null(self::$_objDb)) return self::$_objDb;

        self::$_objDb = new self(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
        if(!self::$_objDb->isconnected()) trigger_error('Database connection error, please contact administrator', E_USER_ERROR);

        return self::$_objDb;
    }

    /**
     * Indique si une connexion en cours à la base de données
     *
     * @return boolean true s'il existe une connexion à la base de donnée
     */

    public static function connected() {
        return !is_null(self::$_objDb);
    }

    /**
     * Retourne le connecteur vers la base de données
     *
     * @reeturn mysqli
     */

    public function getconnector() {
        return $this->mysqli;
    }

    /**
     * Choix d'une base
     *
     * @param string $database nom de la base de données
     * @return boolean true si sélection ok
     */

    public function selectdb($database)
    {
        if (!$this->isconnected()) return false;

        $this->database = $database;

        return($this->mysqli->select_db($this->database));
    }

    /**
     * Détermine si la connexion est active
     *
     * @return boolean true si la connexion est active, false sinon
     */

    public function isconnected()
    {
        return !is_null($this->mysqli);
    }

    /**
     * Ferme la connexion à la base de données
     *
     * @return boolean true si la connexion a été fermée
     */

    public function close()
    {
        if (!$this->isconnected()) return false;
        $this->timer_start();

        if(is_object($this->query_result)) $this->query_result->free();

        $res = $this->mysqli->close();

        $this->timer_stop();

        return $res;
    }


    /**
     * Nouvelle transaction
     */

    public function begin_transaction() {

        if (!$this->isconnected()) return false;

        return $this->mysqli->begin_transaction();

    }

    /**
     * Valide une transaction
     */

    public function commit() {

        if (!$this->isconnected()) return false;

        return $this->mysqli->commit();
    }

    /**
     * Annule une transaction
     */

    public function rollback() {

        if (!$this->isconnected()) return false;

        return $this->mysqli->rollback();
    }


    /**
     * Exécute une requête SQL
     *
     * @param string $query requête SQL à exécuter
     * @return mixed un pointeur sur le recordset (resource) ou false si la requête n'a pas pu être exécutée
     */

    public function query($query = '')
    {
        if (empty($query)) return false;

        if (!$this->isconnected()) return false;

        if($query != '')
        {
            $this->num_queries++;

            $this->timer_start();

            $this->query_result = null;


            $res = false;

            if ($res = $this->mysqli->real_query($query)) {
                $this->query_result = $this->mysqli->store_result();
                if ($this->log) {
                    $stop = $this->timer_stop();

                    $arrTrace = debug_backtrace();
                    $arrFiles = [];
                    // parse debug trace
                    $s = sizeof($arrTrace);
                    for ($key = $s-1; $key>=0; $key--) {
                        if (!empty($arrTrace[$key]['file']) && !empty($arrTrace[$key]['line'])) {
                            $arrFiles[] = sprintf("%s (%s)", $arrTrace[$key]['file'],  $arrTrace[$key]['line']);
                        }
                    }

                    $this->arrLog[] = array ('query' => $query, 'time' => $stop, 'trace' => $arrFiles);
                }
            }
            else {
                // Deadlock found when trying to get lock; try restarting transaction  ?
                // On lance la requête 1 nouvelle fois
                // On applique également le principe en cas de table manquante (parfois juste en phase de regénération)
                $arrFiles = [];

                if ($this->log) {
                    $arrTrace = debug_backtrace();
                    // parse debug trace
                    $s = sizeof($arrTrace);
                    for ($key = $s-1; $key>=0; $key--) {
                        if (!empty($arrTrace[$key]['file']) && !empty($arrTrace[$key]['line'])) {
                            $arrFiles[] = sprintf("%s (%s)", $arrTrace[$key]['file'],  $arrTrace[$key]['line']);
                        }
                    }

                    $strLogFile = _PLOOPI_PATHDATA.'/mysql_error.log';
                    file_put_contents($strLogFile, date('Y-m-d H:i:s')." ".$this->mysqli->error."\r\n".implode("\r\n", $arrFiles)."\r\nQuery: ".trim($query)."\r\n", FILE_APPEND);
                }

                $count = 0;
                if (in_array($this->mysqli->errno, array(1205, 1213, 1146, 2006))) {
                    while (in_array($this->mysqli->errno, array(1205, 1213, 1146, 2006)) && $count++ < 5) {
                        // sleep($count);
                        usleep($count*10000);
                        $this->timer_start();

                        if (($res = $this->mysqli->real_query($query))) {
                            $this->query_result = $this->mysqli->store_result();
                            $stop = $this->timer_stop();

                            if ($this->log) {
                                $this->arrLog[] = array ('query' => $query, 'time' => $stop, 'trace' => $arrFiles);
                            }
                        }
                        else {
                            $stop = $this->timer_stop();
                        }
                    }
                }

                if (!$res) {
                    $this->last_error = $this->mysqli->error;
                    $this->last_errorno = $this->mysqli->errno;
                    $this->last_query = $query;
                    trigger_error($this->mysqli->error.' ('.$this->mysqli->errno.") (Retries:".$count.")<br /><b>query:</b> {$query}", E_USER_WARNING);
                }
            }
        }

        return $this->query_result;
    }


    /**
     * Exécute plusieurs requêtes SQL
     *
     * @param string $queries requêtes
     * @return mixed un pointeur sur le recordset (resource) ou false si la requête n'a pas pu être exécutée
     */

    public function multiplequeries($query = '')
    {
        if (empty($query)) return false;

        if (!$this->isconnected()) return false;

        if($query != '')
        {
            $this->num_queries++;

            $this->timer_start();

            $this->query_result = array();

            if ($this->mysqli->multi_query($query)) {
                do {
                    $this->query_result[] = $this->mysqli->store_result();
                } while ($this->mysqli->next_result());

                $stop = $this->timer_stop();

                if ($this->log) {
                    $arrTrace = debug_backtrace();
                    $arrFiles = [];
                    // parse debug trace
                    $s = sizeof($arrTrace);
                    for ($key = $s-1; $key>=0; $key--) {
                        if (!empty($arrTrace[$key]['file']) && !empty($arrTrace[$key]['line'])) {
                            $arrFiles[] = sprintf("%s (%s)", $arrTrace[$key]['file'],  $arrTrace[$key]['line']);
                        }
                    }

                    $this->arrLog[] = array ('query' => $query, 'time' => $stop, 'trace' => $arrFiles);
                }
            }
            else {
                $this->last_error = $this->mysqli->error;
                $this->last_errorno = $this->mysqli->errno;
                $this->last_query = $query;

                $stop = $this->timer_stop();
                trigger_error($this->mysqli->error."<br /><b>query:</b> {$query}", E_USER_WARNING);
            }
        }

        return $this->query_result;
    }

    /**
     * Parse une suite de requêtes SQL et retourne un tableau de requêtes uniques.
     * Source : https://stackoverflow.com/questions/14257663/letting-a-php-multi-query-continue-if-errors-occur
     * @param string $queries requêtes
     * @return array tableau de requêtes
     */

    public static function split_sql($queries) {
        // Return array of ; terminated SQL statements in $queries.
        $regex = '%(?#!php/x re_split_sql Rev:20170816_0600)
            # Match an SQL record ending with ";"
            \s*                                     # Discard leading whitespace.
            (                                       # $1: Trimmed non-empty SQL record.
              (?:                                   # Group for content alternatives.
                \'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'  # Either a single quoted string,
              | "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"      # or a double quoted string,
              | /\*[^*]*\*+(?:[^*/][^*]*\*+)*/      # or a multi-line comment,
              | \#.*                                # or a # single line comment,
              | --.*                                # or a -- single line comment,
              | [^"\';#]                            # or one non-["\';#-]
              )+                                    # One or more content alternatives
              (?:;|$)                               # Record end is a ; or string end.
            )                                       # End $1: Trimmed SQL record.
            %x';  // End $re_split_sql.
        if (preg_match_all($regex, $queries, $matches)) {
            return $matches[1];
        }
        return array();
    }


    /**
     * Renvoie le nombre d'enregistrement de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @return mixed nombre de lignes dans le recordset ou false si le recordset n'est pas valide
     */

    public function numrows($query_id = null)
    {
        if (!$this->isconnected()) return false;

        if(is_null($query_id)) $query_id = $this->query_result;

        if(!is_object($query_id)) return false;

        return $query_id->num_rows;
    }

    /**
     * Renvoie le nombre de lignes affectées par la dernière requête
     *
     * @return mixed nombre de lignes affectées par la dernière requête
     */

    public function affectedrows()
    {
        if (!$this->isconnected()) return false;

        return $this->mysqli->affected_rows;
    }

    /**
     * Renvoie l'enregistrement courant de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @return mixed l'enregistrement courant (sous forme d'un tableau associatif) ou false si le recordset n'est pas valide
     */

    public function fetchrow($query_id = null, $result_type = MYSQLI_ASSOC)
    {
        if (!$this->isconnected()) return false;

        if(is_null($query_id)) $query_id = $this->query_result;

        if(!is_object($query_id)) return false;

        return $query_id->fetch_array($result_type);
    }

    /**
     * Retourne le dernier id inséré
     *
     * @return mixed dernier id inséré ou false si la connexion n'est pas valide
     */

    public function insertid()
    {
        if (!$this->isconnected()) return false;

        return $this->mysqli->insert_id;
    }

    /**
     * Renvoie la liste des tables de la base de données sélectionnée
     *
     * @return array tableau indexé contenant les tables de la base de données sélectionnée
     */

    public function listtables()
    {
        if (!$this->isconnected()) return false;

        $rs = $this->query("SHOW TABLES FROM `{$this->database}`");

        return $this->getarray($rs);
    }

    /**
    * Renvoie la liste des champs d'une table dont le nom est passé en paramètre
    *
    * @return array tableau à une dimension contenant le nom des champs de la table
    */
     public function listfields($table)
    {
        if (!$this->isconnected()) return false;
        $rs = $this->query("SHOW FIELDS FROM `{$table}`");
        $allResults = $this->getarray($rs);
        $allFields = array();
        foreach ($allResults as $result) $allFields[] = $result['Field'];
        return $allFields;
    }

    /**
     * Renvoie le nombre de champs de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @return mixed nombre de champs ou false si le recordset n'est pas valide
     */

    public function numfields($query_id = null)
    {
        if (!$this->isconnected()) return false;

        if(is_null($query_id)) $query_id = $this->query_result;

        if(!is_object($query_id)) return false;

        return $query_id->field_count;
    }

    /**
     * Renvoie le nom du champs de la dernière requête ou du recordset passé en paramètre selon son indice
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @param integer $i indice du champs
     * @return mixed
     */

    public function fieldname($query_id = null, $i)
    {
        if (!$this->isconnected()) return false;

        if(is_null($query_id)) $query_id = $this->query_result;

        if(!is_object($query_id)) return false;

        $fields = $query_id->fetch_fields();

        return isset($fields[$i]) ? $fields[$i]->name : false;
    }

    /**
     * Retourne dans un tableau le contenu de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @param boolean $firstcolkey true si la première colonne doit servir d'index pour le tableau (optionnel)
     * @return mixed un tableau indexé contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */

    public function getarray($query_id = null, $firstcolkey = false)
    {
        if (!$this->isconnected()) return false;

        if(is_null($query_id)) $query_id = $this->query_result;

        $array = array();

        if ($this->numrows($query_id))
        {
            $this->dataseek($query_id, 0);
            while ($fields = $this->fetchrow($query_id))
            {
                if ($firstcolkey)
                {
                    $key = current($fields);
                    array_shift($fields);

                    if (sizeof($fields) == 0) $array[$key] = $key;
                    elseif (sizeof($fields) == 1) $array[$key] = $fields[key($fields)];
                    else $array[$key] = $fields;
                }
                else $array[] = $fields;
            }

            $this->dataseek($query_id, 0);
        }

        return $array;
    }

    /**
     * Retourne dans une chaine le contenu de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @param string $gluebloc ',' jointure des bloc d'enregistrement
     * @param string $gluevalue ',' jointure des valeur dans les enregsitrements
     * @return string une chaine contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */

    public function getimplode($query_id = 0, $gluebloc=',', $gluevalue=',')
    {
        if (!$this->isconnected()) return false;

        if(is_null($query_id)) $query_id = $this->query_result;

        if(!is_object($query_id)) return false;

        $string = '';
        $boogluebloc = false;

        if ($this->numrows())
        {
            $this->dataseek($query_id, 0);
            while ($fields = $this->fetchrow($query_id))
            {
                // ajout de la glue de bloc d'enregistrement
                if($string != '')
                {
                    $string .= $gluebloc;
                    $boogluebloc = true;
                }

                foreach($fields as $value)
                {
                    // ajout de la glue entre les valeurs
                    if($string != '' && !$boogluebloc) $string .= $gluevalue;
                    // ajout de la valeur
                    $string .= $value;
                    $boogluebloc = false;
                }
            }
            $this->dataseek($query_id, 0);
        }

        return $string;
    }

    /**
     * Retourne au format JSON le contenu de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @param boolean $utf8 true si le contenu doit être encodé en utf8, false sinon (true par défaut)
     * @return string une chaîne au format JSON contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */

    public function getjson($query_id = null, $utf8 = false)
    {
        if (!$this->isconnected()) return false;

        if(is_null($query_id)) $query_id = $this->query_result;

        if(!is_object($query_id)) return false;

        $array = array();

        if ($this->numrows())
        {
            $this->dataseek($query_id, 0);
            while ($fields = $this->fetchrow($query_id))
            {
                if ($utf8) foreach($fields as $key => $value) $fields[$key] = utf8_encode($value);

                if (sizeof($fields) == 1) $array[] = $fields[key($fields)];
                else $array[] = $fields;
            }
        }
        return json_encode($array);
    }

    /**
     * Déplace le pointeur interne sur un enregistrement de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @param integer $pos position dans le recordset
     * @return boolean true si le déplacement a été effectué sinon false
     */

    public function dataseek($query_id = null, $pos = 0)
    {
        if (!$this->isconnected()) return false;

        if(is_null($query_id)) $query_id = $this->query_result;

        if(!is_object($query_id)) return false;

        return $query_id->data_seek($pos);
    }

    /**
     * Protège les caractères spéciaux d'une chaîne de caractère
     *
     * @param string $strVar chaîne à échapper
     * @return chaîne échappée
     */
    public function escape_string($strVar)
    {
        return $this->mysqli->real_escape_string($strVar);
    }

    /**
     * Protège les caractères spéciaux d'une variable
     *
     * @param mixed $var variable à échapper
     * @return mixed variable échappée ou false si la connexion est fermée
     */

    public function addslashes($var)
    {
        if (!$this->isconnected()) return false;

        return is_array($var) ? arr::map(array($this, 'escape_string'), $var) : $this->escape_string($var);
    }

    /**
     * Démarre le timer
     *
     * @see timer
     * @see timer::start
     */

    public function timer_start()
    {
        $this->db_timer = new timer();
        $this->db_timer->start();
    }

    /**
     * Met à jour le temps d'exécution global avec le timer en cours
     *
     * @return int temps écoulé en microsecondes
     *
     * @see timer
     * @see timer::getexectime
     */

    public function timer_stop()
    {
        $intExt = 0;
        $intExt = $this->db_timer->getexectime();
        $this->exectime_queries += $intExt;

        return $intExt;
    }

    public function set_charset($charset) { $this->mysqli->set_charset($charset); }

    public function get_num_queries() { return($this->num_queries); }

    public function get_exectime_queries() { return($this->exectime_queries); }

    public function get_log() { return $this->arrLog; }

    public function enable_log($log) { $this->log = $log; }

    public function flush_log() { $this->arrLog = array(); }

    public function get_last_error() { return($this->last_error); }
    public function get_last_errorno() { return($this->last_errorno); }
    public function get_last_query() { return($this->last_query); }
}
