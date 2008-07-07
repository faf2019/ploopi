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

/**
 * Gestion de la connexion à la base MySQL.
 * 
 * @package ploopi
 * @subpackage database
 * @copyright Netlor, Ovensia
 * @license GNU General var License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe MySQL d'accès aux données.
 * Permet de se connecter, d'exécuter des requêtes, etc...
 * 
 * @package ploopi
 * @subpackage database
 * @copyright Netlor, Ovensia
 * @license GNU General var License (GPL)
 * @author Stéphane Escaich
 */

class ploopi_db
{
    /**
     * Détermine si la connexion est permanente
     *
     * @var boolean
     */
    
    private $persistency;

    /**
     * Nom d'utilisateur pour la connexion à la BDD
     *
     * @var string
     */
    
    private $user;

    /**
     * Mot de passe pour la connexion à la BDD
     *
     * @var string
     */
    
    private $password;

    /**
     * Nom du serveur (hôte, ip) pour la connexion à la BDD
     *
     * @var string
     */
    
    private $server;

    /**
     * Nom de la base de données pour la connexion à la BDD
     *
     * @var string
     */
    
    private $database;

    /**
     * Identifiant de connexion MySQL
     *
     * @var ressource
     */
    
    private $connection_id;

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
    
    private $db_timer;

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
    
    public function ploopi_db($server, $user, $password = '', $database = '', $persistency = false)
    {
        $this->persistency = $persistency;
        $this->user = $user;
        $this->password = $password;
        $this->server = $server;
        $this->database = $database;
        $this->connection_id = 0;
        $this->num_queries = 0;
        $this->exectime_queries = 0;


        if($this->persistency)
        {
            $this->timer_start();
            $this->connection_id = @mysql_pconnect($this->server, $this->user, $this->password);
            $this->timer_stop();
        }
        else
        {
            $this->timer_start();
            $this->connection_id = @mysql_connect($this->server, $this->user, $this->password);
            $this->timer_stop();
        }

        if (mysql_errno() != 0) trigger_error(mysql_error(), E_USER_ERROR);

        if($this->connection_id)
        {
            if($this->database != '')
            {
                $this->timer_start();

                $dbselect = @mysql_select_db($this->database);

                $this->timer_stop();

                if(!$dbselect)
                {
                    @mysql_close();
                    $this->connection_id = $dbselect;
                    return false;
                }
            }
            else return false;
            return $this->connection_id;
        }
        else return false;
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
        return(@mysql_select_db($this->database, $this->connection_id));
    }

    /**
     * Détermine si la connexion est active
     *
     * @return boolean true si la connexion est active, false sinon
     */
    
    public function isconnected()
    {
        return ($this->connection_id != 0);
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

        if(is_resource($this->query_result))
        {
            @mysql_free_result($this->query_result);
        }
        $query_result = @mysql_close($this->connection_id);

        $this->timer_stop();

        return $query_result;
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
        
        unset($this->query_result);
        
        if (!$this->isconnected()) return false;
                
        if($query != '')
        {
            $this->num_queries++;

            $this->timer_start();

            @mysql_select_db($this->database, $this->connection_id);
            $this->query_result = @mysql_query($query, $this->connection_id);

            $this->timer_stop();

            if (mysql_errno() != 0) trigger_error(mysql_error()."<br />ressource:{$this->connection_id}<br /><b>query:</b> $query", E_USER_WARNING);

        }

        if($this->query_result) return $this->query_result;
        else return false;
    }


    /**
     * Exécute plusieurs requêtes SQL
     *
     * @param string $queries requêtes
     * @return boolean true si les requêtes ont pu être exécutées, false sinon
     */
    public function multiplequeries($queries)
    {
        if (!$this->isconnected()) return false;
                
        $queries_array = explode("\n",$queries);

        $query = '';

        // on parse le contenu SQL
        foreach($queries_array as $sql_line)
        {
            if(trim($sql_line) != "" && strpos($sql_line, "--") === false)
            {
                $query .= $sql_line;

                // on verifie que la ligne est une requete valide
                if(preg_match("/(.*);/", $sql_line))
                {
                    $query = substr($query, 0, strlen($query)-1);

                    // et on execute !
                    $this->query($query);
                    $query = "";
                }
            }
        }

        return (true);
    }

    /**
     * Renvoie le nombre d'enregistrement de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @return mixed nombre de lignes dans le recordset ou false si le recordset n'est pas valide
     */
    
    public function numrows($query_id = 0)
    {
        if (!$this->isconnected()) return false;
                
        if(!$query_id) $query_id = $this->query_result;
            
        if($query_id) return @mysql_num_rows($query_id);
        else return false;
    }
    
    /**
     * Renvoie l'enregistrement courant de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @return mixed l'enregistrement courant (sous forme d'un tableau associatif) ou false si le recordset n'est pas valide
     */
    
    public function fetchrow($query_id = 0)
    {
        if (!$this->isconnected()) return false;
                
        if(!$query_id) $query_id = $this->query_result;
        
        if($query_id) return mysql_fetch_array($query_id, MYSQL_ASSOC);
        else return false;
    }

    /**
     * Retourne le dernier id inséré
     *
     * @return mixed dernier id inséré ou false si la connexion n'est pas valide
     */
    
    public function insertid()
    {
        if (!$this->isconnected()) return false;
        return @mysql_insert_id($this->connection_id);
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
     * Renvoie le nombre de champs de la dernière requête ou du recordset passé en paramètre
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @return mixed nombre de champs ou false si le recordset n'est pas valide
     */
    
    public function numfields($query_id = 0)
    {
        if (!$this->isconnected()) return false;
                
        if(!$query_id) $query_id = $this->query_result;

        if($query_id) return @mysql_num_fields($query_id);
        else return false;
    }

    /**
     * Renvoie le nom du champs de la dernière requête ou du recordset passé en paramètre selon son indice 
     * 
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @param integer $i indice du champs
     * @return mixed
     */
    
    public function fieldname($query_id = 0, $i)
    {
        if (!$this->isconnected()) return false;
        
        if(!$query_id) $query_id = $this->query_result;

        if($query_id) return @mysql_field_name($query_id, $i);
        else return false;
    }

    /**
     * Retourne dans un tableau le contenu de la dernière requête ou du recordset passé en paramètre 
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @return mixed un tableau indexé contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */
    
    public function getarray($query_id = 0)
    {
        if (!$this->isconnected()) return false;
        
        if(!$query_id) $query_id = $this->query_result;

        if($query_id)
        {
            $array = array();

            if ($this->numrows())
            {
                $this->dataseek($query_id, 0);
                while ($fields = $this->fetchrow($query_id))
                {
                    if (sizeof($fields) == 1) $array[] = $fields[key($fields)];
                    else $array[] = $fields;
                }
            }
            return $array;
        }
        else return false;
    }

    /**
     * Retourne dans au format JSON le contenu de la dernière requête ou du recordset passé en paramètre 
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @param boolean $utf8 true si le contenu doit être encodé en utf8, false sinon (true par défaut)
     * @return string une chaîne au format JSON contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */
    
    public function getjson($query_id = 0, $utf8 = true)
    {
        if (!$this->isconnected()) return false;
        
        if(!$query_id) $query_id = $this->query_result;

        if($query_id)
        {
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
        else return false;
    }

    /**
     * Déplace le pointeur interne sur un enregistrement de la dernière requête ou du recordset passé en paramètre 
     *
     * @param resource $query_id recordset (optionnel), sinon prend le recordset de la dernière requête exécutée
     * @param integer $pos position dans le recordset
     * @return boolean true si le déplacement a été effectué sinon false
     */
    
    public function dataseek($query_id = 0, $pos = 0)
    {
        if (!$this->isconnected()) return false;
        
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) return @mysql_data_seek($query_id, $pos);
        else return(false);

    }

    /**
     * Protège les caractères spéciaux d'une commande SQL
     *
     * @param mixed $var variable à échapper
     * @return mixed variable échappée ou false si la connexion est fermée
     */
    
    public function addslashes($var)
    {
        include_once './include/functions/system.php';
        
        if ($this->isconnected()) return(ploopi_array_map('mysql_real_escape_string', $var));
        else return(false);
    }

    /**
     * Démarre le timer
     * 
     * @see timer
     * @see timer::start
     */
    
    public function timer_start()
    {
        if (class_exists('timer'))
        {
            $this->db_timer = new timer();
            $this->db_timer->start();
        }
    }

    /**
     * Met à jour le temps d'exécution global avec le timer en cours
     * 
     * @see timer
     * @see timer::getexectime
     */
    
    public function timer_stop()
    {
        if (class_exists('timer'))
        {
            $this->exectime_queries += $this->db_timer->getexectime();
        }
    }
    
    public function get_num_queries() { return($this->num_queries); }
    
    public function get_exectime_queries() { return($this->exectime_queries); }
    

}
?>
