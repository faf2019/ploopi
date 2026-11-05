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
 * Gestion générique de l'accès aux données (Mapping objet-relationnel / ORM).
 * Permet la manipulation d'enregistrements de base de données sous forme d'objets.
 *
 * @package ploopi
 * @subpackage database
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class data_object
{
    /** Préfixe métier et champs dont les changements justifient un événement ; activation explicite par classe. */
    protected $strEventPrefix = '';
    protected $arrEventFields = array();

    /**
     * Nom de la classe
     *
     * @var string
     */

    protected $classname;

    /**
     * Nom de la table
     *
     * @var string
     */

    protected $tablename;

    /**
     * Nom de la table (intégration SQL)
     *
     * @var string
     */

    protected $tablename_quoted;

    /**
     * Tableau indexé des champs qui composent la clé primaire
     *
     * @var array
     */

    protected $idfields;

    /**
     * Tableau associatif des valeurs qui composent la clé primaire
     *
     * @var array
     */

    protected $id;

    /**
     * Objet de connexion à la base de données
     *
     * @var ploopi\db
     */

    private $db;

    /**
     * Recordset
     *
     * @var resource
     */

    private $resultid;

    /**
     * Nombre de ligne du dernier résultat
     *
     * @var int
     */

    private $numrows;

    /**
     * Requête SQL générée
     *
     * @var string
     */

    private $sql;

    /**
     * Valeurs telles qu'en base au dernier open(), open_row() ou save() réussi :
     * save() n'écrit que les champs qui en diffèrent. null : inconnues, tout est écrit.
     *
     * @var array|null
     */

    private $arrOriginal = null;

    /**
     * Valeurs initiales par table, calculées par init_description() (un DESCRIBE par
     * table et par requête HTTP). Vidé par db à chaque ordre de structure (ALTER, CREATE…).
     *
     * @var array
     */

    private static $arrDescriptions = array();

    /**
     * Contenu d'un enregistrement de la table dans un tableau associatif : champ => valeur
     *
     * @var array
     */

    public $fields;

    /**
     * Indique s'il s'agit d'un nouvel enregistrement (true) ou d'un enregistrement existant (false)
     *
     * @var boolean
     */

    public $new;

    /**
     * Constructeur de la classe
     *
     * @param string nom de la table
     * @param string champ clé n°1
     * @param string champ clé n°2
     * @param string champ clé n°X
     *
     * @return data_object
     *
     */

    public function __construct()
    {
        $db = db::get();
        // arg(0) : tablename

        $numargs = func_num_args();
        $this->classname = get_class($this) ;
        $this->tablename = func_get_arg(0);
        $this->tablename_quoted = '`'.str_replace('.', '`.`', $this->tablename).'`';
        $this->idfields = array();
        $this->id = array();
        $this->fields = array();

        if ($numargs == 1) // cas particulier n°1, pas de clé définie, on prend id par défaut
        {
            $this->idfields[0] = 'id';
            $this->fields['id'] = null;
        }
        else
        {
            if ($numargs == 2 && is_array($keys = func_get_arg(1))) // cas particulier n°2, les clés sont définies dans un tableau (nouvelle méthode)
            {
                if (sizeof($keys) == 0) // cas particulier n°2b, pas de clé définie, on prend id par défaut
                {
                    $this->idfields[0] = 'id';
                    $this->fields['id'] = null;
                }
                else
                {
                    $i = 0;
                    foreach(func_get_arg(1) as $key)
                    {
                        $this->idfields[$i] = $key;
                        $this->fields[$this->idfields[$i]] = null;
                        $this->id[$this->idfields[$i]] = null;
                        $i++;
                    }
                }
            }
            else // cas général, les clés sont définies dans une liste d'arguments
            {
                for ($i = 1; $i < $numargs; $i++)
                {
                    if (!is_null(func_get_arg($i)))
                    {
                        $this->idfields[$i-1] = func_get_arg($i);
                        $this->fields[$this->idfields[$i-1]] = null;
                        $this->id[$this->idfields[$i-1]] = null;
                    }
                }
            }
        }

        $this->db = &$db;

        $this->new = true;
    }


    /**
     * Permet de modifier la connexion à la base de données
     *
     * @param ressource $db objet de connexion à la base de données
     */

    public function setdb($db)
    {
        unset($this->db);
        $this->db = $db;

        // La clé du cache (spl_object_hash) peut être réattribuée à une autre connexion.
        self::reset_descriptions();
    }


    /**
     * Permet de mettre à jour les propriétés de l'objet (les champs de la table)
     *
     * @param array $values tableau associatif contenant les valeurs tel que "prefixe_nomduchamp" => "valeur"
     * @param string $prefix préfixe utilisé
     */

    public function setvalues($values, $prefix = '')
    {
        // par défaut on récupère les champs du formulaire ($values)
        $lprefix = strlen($prefix);
        foreach ($values as $key => $value)
        {
            // Les clés deviennent des noms de colonnes : seule une clé qui peut en être un est reprise.
            if (!preg_match('/^[A-Za-z0-9_]+\z/', $key)) continue;

            if ($lprefix == 0) $this->fields[$key] = $value;
            else
            {
                $pref = substr($key, 0, $lprefix);
                if ($pref == $prefix)
                {
                    $prop = substr($key, $lprefix);
                    $this->fields[$prop] = $value;
                }
            }
        }

    }

    /**
     * Ouvre un enregistrement de la table et met à jour l'objet
     *
     * @param mixed valeur du champ 1 de la clé
     * @param mixed valeur du champ 2 de la clé
     * @param mixed valeur du champ X de la clé
     *
     * @return int nombre d'enregistrements
     */

    public function open(...$args) // id0, id1, id2, etc...
    {
        if(sizeof($args) == 1 && is_array($args[0])) $args = $args[0];

        $numargs = sizeof($args);

        if ($numargs == sizeof($this->idfields))
        {
            for ($i = 0; $i < $numargs; $i++) $id[$i] = $args[$i];

            $this->sql = "SELECT * FROM {$this->tablename_quoted} WHERE ".self::quotename($this->idfields[0])." = '".$this->db->addslashes($id[0])."'";

            for ($i = 1; $i < $numargs; $i++) $this->sql .= " AND ".self::quotename($this->idfields[$i])." = '".$this->db->addslashes($id[$i])."'";

            $this->resultid = $this->db->query($this->sql);
            $this->numrows = $this->db->numrows($this->resultid);
            $this->fields = $this->db->fetchrow($this->resultid);

            // Lu avant la réaffectation des clés : la copie garde les valeurs exactes de la base.
            $this->arrOriginal = $this->numrows > 0 ? $this->fields : null;

            for ($i = 0; $i < $numargs; $i++) $this->id[$this->idfields[$i]] = $this->fields[$this->idfields[$i]] = $id[$i];

            // Un objet réutilisé après une ouverture réussie redevient neuf si la clé est absente.
            $this->new = !($this->numrows > 0);

            return $this->numrows > 0;
        }
        else return false;

    }

    /**
     * Méthode d'ouverture spéciale pour "convertir" une ligne de recordset en objet
     *
     * @param array $row ligne de recordset
     */

    public function open_row($row)
    {
        $this->fields = $this->arrOriginal = $row;

        foreach($this->idfields as $field) $this->id[$field] = $row[$field];

        $this->new = false;
    }

    /**
     * Insère ou met à jour l'enregistrement dans la base de données
     *
     * La mise à jour n'écrit que les champs modifiés depuis open(), open_row() ou le
     * dernier save() ; sans modification, aucune requête n'est émise. Un objet dont les
     * valeurs en base sont inconnues (new mis à false à la main) est écrit en entier.
     *
     * @return mixed valeur de la clé primaire ; false si l'insertion a échoué
     * (l'objet reste alors neuf)
     */

    public function save()
    {
        if ($this->strEventPrefix === '' || !events::enabled()) return $this->save_record();
        $booNew = $this->new;
        $arrChanged = array();
        foreach ($this->arrEventFields as $strField)
        {
            if (array_key_exists($strField, $this->fields) && ($booNew || !is_array($this->arrOriginal) || !array_key_exists($strField, $this->arrOriginal) || !self::samevalue($this->fields[$strField], $this->arrOriginal[$strField]))) $arrChanged[] = $strField;
        }
        if (!$booNew && !$arrChanged) return $this->save_record();
        // Restaurer l'état ORM si l'outbox échoue : l'objet peut être réessayé.
        $arrBefore = array($this->new, $this->fields, $this->id, $this->arrOriginal);
        try
        {
            return $this->db->transaction(function () use ($booNew, $arrChanged)
            {
                $mixId = $this->save_record();
                if ($mixId === false) return false;
                events::record($this->strEventPrefix.($booNew ? '.created.v1' : '.updated.v1'), array('id' => count($this->id) === 1 ? reset($this->id) : $this->id, 'changed_fields' => $arrChanged), $this->event_context());
                return $mixId;
            });
        }
        catch (\Throwable $objError)
        {
            list($this->new, $this->fields, $this->id, $this->arrOriginal) = $arrBefore;
            throw $objError;
        }
    }

    /** Écriture SQL, partagée avec le chemin transactionnel d'événement. */
    protected function save_record()
    {

        if ($this->new) // insert
        {
            $listvalues='';

            // build insert
            $arrValues = array();
            foreach ($this->fields as $key => $value)
            {
                $arrValues[] = self::sqlvalue($this->tablename_quoted, $key, $value, $this->db);
            }

            $listvalues = (empty($arrValues)) ? '' : 'SET '.implode(', ', $arrValues);

            $this->sql = "INSERT INTO {$this->tablename_quoted} {$listvalues}"; // construction de la requète

            // db::query() rend null sur une erreur SQL, false sur un INSERT réussi (et aussi,
            // hélas, sans connexion : ce cas-là n'est pas détecté). Marquer l'objet comme
            // enregistré après une erreur ferait d'un second save() un UPDATE sur une clé vide.
            if (is_null($this->db->query($this->sql))) return false;

            // get "static" key
            foreach($this->idfields as $fieldname) if (isset($this->fields[$fieldname])) $this->id[$fieldname] = $this->fields[$fieldname];

            // get insert id from insert (if 1 field primary key and autokey)
            if (sizeof($this->idfields) >= 1 && $this->db->insertid() !== 0)
            {
                $this->id[$this->idfields[0]] = $this->fields[$this->idfields[0]] = $this->db->insertid();
            }

            $this->new = false;
            $this->arrOriginal = $this->fields;
        }
        else // update
        {
            $listvalues='';
            $arrValues = array();
            foreach ($this->fields as $key => $value)
            {
                if (is_array($this->arrOriginal) && array_key_exists($key, $this->arrOriginal)
                    && self::samevalue($this->arrOriginal[$key], $value)) continue;

                $arrValues[] = self::sqlvalue($this->tablename_quoted, $key, $value, $this->db);
            }

            if (!empty($arrValues))
            {
                $listvalues = implode(', ', $arrValues);

                // build request
                $this->sql = "UPDATE {$this->tablename_quoted} SET {$listvalues} WHERE ".$this->sqlkey();

                if (is_null($this->db->query($this->sql))) return false;
                else
                {
                    $this->arrOriginal = $this->fields;
                    // La clé a pu être modifiée par la mise à jour : les suivantes la visent.
                    foreach($this->idfields as $fieldname) if (isset($this->fields[$fieldname])) $this->id[$fieldname] = $this->fields[$fieldname];
                }
            }
        }

        // return key (array if multiple key)
        if (sizeof($this->idfields) == 1) return ($this->fields[$this->idfields[0]]);
        else
        {
            $res = array();
            foreach($this->idfields as $idfield) $res[] = $this->fields[$idfield];
            return ($res);
        }
    }

    /**
     * Rend une affectation « table.champ = valeur » pour une requête INSERT ou UPDATE
     *
     * Un booléen PHP ne peut pas être inséré tel quel : la conversion implicite en
     * chaîne donne '1' pour true mais la CHAÎNE VIDE pour false, qu'un serveur en mode
     * strict — STRICT_TRANS_TABLES, défaut depuis MySQL 5.7 et MariaDB 10.2 — refuse
     * sur une colonne numérique. Tout champ affecté à false était donc silencieusement
     * impossible à enregistrer ; c'est ce qui empêchait, par exemple, de déverrouiller
     * une page de wiki.
     *
     * @param string $strTable nom de table déjà protégé
     * @param string $strChamp nom du champ
     * @param mixed $mixValeur valeur à écrire
     * @param db $objDb connexion, pour l'échappement
     * @return string affectation SQL
     */

    protected static function sqlvalue($strTable, $strChamp, $mixValeur, $objDb)
    {
        $strChamp = self::quotename($strChamp);

        if (is_null($mixValeur)) return "{$strTable}.{$strChamp} = null";

        if (is_bool($mixValeur)) return "{$strTable}.{$strChamp} = ".($mixValeur ? '1' : '0');

        return "{$strTable}.{$strChamp} = '".$objDb->addslashes($mixValeur)."'";
    }

    /**
     * Indique si deux valeurs s'enregistrent à l'identique
     *
     * Comparaison telle que la base la verra : 3 et '3', false et '0', true et '1' sont
     * égaux ; null n'est égal qu'à null (null et '' diffèrent en base).
     *
     * @param mixed $mixA valeur lue en base
     * @param mixed $mixB valeur à écrire
     * @return boolean
     */

    private static function samevalue($mixA, $mixB)
    {
        if (is_null($mixA) || is_null($mixB)) return is_null($mixA) && is_null($mixB);

        if (is_bool($mixA)) $mixA = (int) $mixA;
        if (is_bool($mixB)) $mixB = (int) $mixB;

        // Un tableau (saisie multiple) n'est jamais tenu pour inchangé : il est écrit comme avant.
        if (!is_scalar($mixA) || !is_scalar($mixB)) return false;

        return (string) $mixA === (string) $mixB;
    }

    /**
     * Protège un nom de colonne pour l'intégrer à une requête
     *
     * Les noms de champs viennent souvent des clés d'un formulaire (setvalues($_POST)),
     * que security::filtervar() ne nettoie pas. Sans échappement de l'accent grave, une
     * clé telle que « titre`=(SELECT …),`contenu » injectait du SQL dans la requête.
     *
     * @param string $strChamp nom du champ
     * @return string nom protégé, entre accents graves
     */

    protected static function quotename($strChamp)
    {
        return '`'.str_replace('`', '``', $strChamp).'`';
    }

    /**
     * Supprime l'enregistrement dans la base de données
     */

    public function delete()
    {
        $arrArgs = func_get_args();
        if ($this->strEventPrefix === '' || !events::enabled()) return $this->delete_record($arrArgs);
        return $this->db->transaction(function () use ($arrArgs)
        {
            $booDeleted = $this->delete_record($arrArgs);
            if ($booDeleted) events::record($this->strEventPrefix.'.deleted.v1', array('id' => count($this->id) === 1 ? reset($this->id) : $this->id), $this->event_context());
            return $booDeleted;
        });
    }

    private function event_context()
    {
        return array('module' => (int) ($this->fields['id_module'] ?? ($_SESSION['ploopi']['moduleid'] ?? 0)),
            'workspace' => (int) ($this->fields['id_workspace'] ?? ($_SESSION['ploopi']['workspaceid'] ?? 0)),
            'user' => (int) ($_SESSION['ploopi']['userid'] ?? 0));
    }

    private function delete_record($arrArgs)
    {
        foreach ($arrArgs as $intIndex => $mixId) $this->id[$this->idfields[$intIndex]] = $this->fields[$this->idfields[$intIndex]] = $mixId;
        $this->sql = "DELETE FROM {$this->tablename_quoted} WHERE ".$this->sqlkey();
        if (is_null($this->db->query($this->sql))) return false;
        return $this->db->affectedrows() > 0;
    }

    /**
     * Rend la clause WHERE qui désigne l'enregistrement
     *
     * La clé lue par open() / open_row() prime : un champ de clé modifié dans fields
     * (setvalues() d'un formulaire, par exemple) ne détourne ni l'UPDATE ni le DELETE
     * vers un autre enregistrement. À défaut (objet rempli à la main), fields fait foi.
     *
     * @return string condition SQL
     */

    private function sqlkey()
    {
        $arrWhere = array();
        foreach($this->idfields as $fieldname)
        {
            $mixValue = isset($this->id[$fieldname]) ? $this->id[$fieldname] : (isset($this->fields[$fieldname]) ? $this->fields[$fieldname] : '');
            $arrWhere[] = "{$this->tablename_quoted}.".self::quotename($fieldname)." = '".$this->db->addslashes($mixValue)."'";
        }

        return implode(' AND ', $arrWhere);
    }

    /**
     * Initialise les propriétés de l'objet avec la structure et les valeurs par défaut de la table
     *
     * Une colonne sans valeur par défaut déclarée prend la valeur vide qui convient à son
     * type : null pour une colonne auto-incrémentée ou numérique acceptant null, 0 pour une
     * colonne numérique qui ne l'accepte pas, la chaîne vide sinon.
     *
     * Jusqu'ici toute colonne sans défaut recevait la chaîne vide, y compris la clé
     * auto-incrémentée. save() émettait donc « INSERT ... SET `id` = '' », que tout serveur
     * en mode strict — STRICT_TRANS_TABLES, valeur par défaut depuis MySQL 5.7 et
     * MariaDB 10.2 — rejette avec « Incorrect integer value: '' for column id ».
     * L'enchaînement init_description() puis save(), pourtant recommandé pour une insertion,
     * ne fonctionnait plus sur un serveur récent.
     */

    public function init_description()
    {
        $strCle = spl_object_hash($this->db).$this->tablename_quoted;

        if (!isset(self::$arrDescriptions[$strCle]))
        {
            $arrDefauts = array();
            $this->sql = "DESCRIBE {$this->tablename_quoted}";
            $rs = $this->db->query($this->sql);
            while ($fields = $this->db->fetchrow($rs)) {
                $arrDefauts[$fields['Field']] = self::defaultvalue($fields);
            }

            // Une table absente ne se met pas en cache : elle peut être créée plus loin.
            if (empty($arrDefauts)) return;

            self::$arrDescriptions[$strCle] = $arrDefauts;
        }

        foreach(self::$arrDescriptions[$strCle] as $strChamp => $mixValeur) $this->fields[$strChamp] = $mixValeur;
    }

    /**
     * Oublie les descriptions de tables mises en cache par init_description()
     *
     * Appelée par db après tout ordre qui peut changer une structure (ALTER, CREATE,
     * DROP, RENAME) : une installation ou une mise à jour insère souvent dans la table
     * qu'elle vient de modifier, au cours de la même requête.
     */

    public static function reset_descriptions()
    {
        self::$arrDescriptions = array();
    }

    /**
     * Détermine la valeur initiale d'une colonne à partir de sa description (DESCRIBE)
     *
     * @param array $arrField ligne de DESCRIBE : Field, Type, Null, Key, Default, Extra
     * @return mixed valeur par défaut déclarée, ou valeur vide adaptée au type
     */

    protected static function defaultvalue($arrField)
    {
        // Une colonne auto-incrémentée est renseignée par le serveur.
        if (stripos($arrField['Extra'], 'auto_increment') !== false) return null;

        // Valeur par défaut déclarée dans le schéma : elle prime.
        if (!is_null($arrField['Default'])) return trim($arrField['Default'], "'");

        // Sans défaut déclaré, la chaîne vide n'est pas une valeur acceptable pour une
        // colonne numérique : le défaut implicite est null si la colonne l'accepte, 0 sinon.
        if (preg_match('/^(tinyint|smallint|mediumint|int|integer|bigint|decimal|dec|numeric|float|double|real|bit|year)\b/i', $arrField['Type']))
            return strtoupper($arrField['Null']) == 'YES' ? null : 0;

        // Même raisonnement pour les colonnes temporelles, que '' ferait également rejeter.
        if (preg_match('/^(date|datetime|timestamp|time)\b/i', $arrField['Type']) && strtoupper($arrField['Null']) == 'YES')
            return null;

        return '';
    }

    /**
     * Met à jour les propriétés id_user, id_workspace, id_module de l'objet avec le contenu de la session
     */

    public function setuwm()
    {
        $this->fields['id_user'] = $_SESSION['ploopi']['userid'] ;
        $this->fields['id_workspace'] = $_SESSION['ploopi']['workspaceid'];
        $this->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
    }

    /**
     * Génère un dump SQL de l'enregistrement
     *
     * @return string dump SQL
     */

    public function dump()
    {
        $listvalues='';

        // build insert
        foreach ($this->fields as $key => $value)
        {
            if ($key != '' && !is_null($value))
            {
                if ($listvalues != '') $listvalues .= ', ';
                $listvalues .= "{$this->tablename_quoted}.".self::quotename($key)." = '".$this->db->addslashes($value)."'";
            }
        }

        return ("INSERT INTO {$this->tablename_quoted} SET {$listvalues}");
    }

    /**
     * Génère des variables templates à partir des propriétés de l'objet
     *
     * @param Template $tpl template
     * @param string $prefix préfixe à ajouter (optionnel)
     */

    public function totemplate(&$tpl, $prefix = '')
    {
        $array_vars = array();
        foreach($this->fields as $key => $value) $array_vars[strtoupper("{$prefix}{$key}")] = $value;
        $tpl->assign_vars($array_vars);
    }

    /**
     * Retourne le script SQL de création de la table
     *
     * @return string script SQL
     */

    public function getsqlstructure()
    {
        $sql = "CREATE TABLE {$this->tablename_quoted} (";

        $fields = '';

        foreach ($this->fields as $key => $value)
        {
            if ($key != '')
            {
                if ($fields != '') $fields .= ",\n";
                $fields .= self::quotename($key)." varchar(255) NOT NULL default ''";
            }
        }

        // TYPE= n'est plus accepté depuis MySQL 5.5.
        $sql .= $fields.") ENGINE=InnoDB;";

        return($sql);
    }

    /**
     * Retourne la dernière requête SQL exécutée
     *
     * @return string
     */

    public function getsql() { return $this->sql; }

    /**
     * Retourne la table associée à l'instance
     *
     * @return string nom de la table
     */

    public function gettablename() { return $this->tablename; }

    /**
     * Retourne true si l'enregistrement n'existe pas encore dans la base de données
     *
     * @return boolean
     */

    public function isnew() { return $this->new; }

    /**
     * Retourne un hash de la clé de l'enregistrement
     *
     * @return string
     */
    public function gethash()
    {
        $arrHash = array();
        foreach($this->idfields as $fieldname) if (isset($this->id[$fieldname])) $arrHash[] = $this->id[$fieldname];
        return(implode(',', $arrHash));
    }

    /**
     * Retourne la collection
     *
     * @return data_object_collection
     */
    public static function get_collection() {
        return new data_object_collection(get_called_class());
    }

    /**
     * Retourne la liste des objets

     * @return array
     */
    public static function get_objects() {
        $objCol = new data_object_collection(get_called_class());
        return $objCol->get_objects();
    }

}
