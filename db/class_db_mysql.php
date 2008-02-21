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
include_once './include/functions/system.php';

class ploopi_db
{

    /**
    * @var boolean database connection persistance
    * @access public
    */
    var $persistency = true;

    /**
    * @var string db user login
    * @access public
    */
    var $user = '';

    /**
    * @var string db user password
    * @access public
    */
    var $password = '';

    /**
    * @var string database server address
    * @access public
    */
    var $server = '';

    /**
    * @var string database name
    * @access public
    */
    var $database = '';

    /**
    * @var int database connection id
    * @access public
    */
    var $connection_id;

    /**
    * @var int last query resultset id
    * @access public
    */
    var $query_result;

    /**
    * @var int number of queries
    * @access private
    */
    var $num_queries = 0;

    /**
    * @var int execution time took by queries
    * @access private
    */
    var $exectime_queries = 0;

    /**
    * @var array result
    * @access public
    */
    var $array = array();


    var $db_timer;

    /**
    * constructor
    *
    * @param string $server database server address
    * @param string $user user login
    * @param string password user password
    * @param boolean persistency tells if the db connection should be persistant or not
    *
    * @return mixed db connection id if successful, FALSE if not.
    *
    * @access public
    */
    function ploopi_db($server, $user, $password, $database = '', $persistency = false, $newlink = false)
    {


        $this->persistency = $persistency;
        $this->user = $user;
        $this->password = $password;
        $this->server = $server;
        $this->database = $database;
        $this->connection_id = 0;


        if($this->persistency)
        {
            $this->timer_start();
            $this->connection_id = @mysql_pconnect($this->server, $this->user, $this->password, $newlink);
            $this->timer_stop();
        }
        else
        {
            $this->timer_start();
            $this->connection_id = @mysql_connect($this->server, $this->user, $this->password, $newlink);
            $this->timer_stop();
        }

        if (mysql_errno() != 0) trigger_error(mysql_error(), E_USER_ERROR);

        if($this->connection_id)
        {
            if($this->database != "")
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
        else
        {
            return false;
        }

    }

    function selectdb($database)
    {
        if ($this->isconnected())
        {
            $this->database = $database;
            return(@mysql_select_db($this->database, $this->connection_id));
        }
        else return(false);
    }

    function isconnected()
    {
        return ($this->connection_id!=0);
    }

    /**
    * free result pointers (if applicable) and close connection to database
    *
    * @return boolean
    *
    * @access public
    *
    * @uses connection_id
    */
    function close()
    {
        if ($this->isconnected())
        {
            $this->timer_start();

            if(is_resource($this->query_result))
            {
                @mysql_free_result($this->query_result);
            }
            $query_result = @mysql_close($this->connection_id);

            $this->timer_stop();

            return $query_result;
        }
        else return(false);
    }

    /**
    * execute a SQL query
    *
    * @return mixed If successful : resultset id, if no result : FALSE
    *
    * @access public
    *
    * @uses connection_id
    * @uses num_queries
    * @uses query_result
    * @uses row
    * @uses rowset
    */
    function query($query = '')
    {
        unset($this->query_result);

        if ($this->isconnected())
        {
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
            else return (false);
        }
        else return (false);
    }


    /**
    * execute a SQL query
    *
    * @access public
    *
    */

    function multiplequeries($queries)
    {
        if ($this->isconnected())
        {
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
        else return(false);
    }

    /**
    * execute a SQL query
    *
    * @return mixed If successful : number of elements in resultset, else : FALSE
    *
    * @param int query id
    * @access public
    *
    * @uses query_result
    */
    function numrows($query_id = 0)
    {
        if(!$query_id)
        {
            $query_id = $this->query_result;
        }
        if($query_id)
        {
            $result = @mysql_num_rows($query_id);
            return $result;
        }
        else
        {
            return false;
        }
    }


    /**
    * retrieves the resultset
    *
    * @return mixed If successful : array containing the query resultset, else : FALSE
    *
    * @param int query id
    * @param string opt fetching method
    *
    * @access public
    *
    * @uses query_result
    * @uses row
    */
    function fetchrow($query_id = 0, $opt = MYSQL_ASSOC)
    {
        if(!$query_id)
        {
            $query_id = $this->query_result;
        }

        if($query_id) return mysql_fetch_array($query_id, $opt);
        else return false;
    }

    /**
    * retrieves last database insert id
    *
    * @return mixed If successful : last inserted id, else : FALSE
    *
    * @access public
    *
    * @uses connection_id
    */
    function insertid()
    {
        if($this->connection_id)
        {
            $result = @mysql_insert_id($this->connection_id);
            return $result;
        }
        else
        {
            return false;
        }
    }

    /**
    * retrieves a list of database tables
    *
    * @return mixed If successful : result ressource id on database tables list, else : FALSE
    *
    * @access public
    *
    * @uses database
    * @uses connection_id
    */
    function listtables()
    {
        if($this->connection_id)
        {
            $result = @mysql_list_tables($this->database, $this->connection_id);
            return $result;
        }
        else
        {
            return false;
        }
    }

    /**
    * retrieves a table name
    *
    * @return mixed If successful : table name, else : FALSE
    *
    * @param int result ressource id from the mysql_list_tables()
    * @param int index of table
    *
    * @access public
    *
    * @uses connection_id
    */
    function tablename($tables, $i)
    {
        if($this->connection_id)
        {
            $result = @mysql_tablename($tables, $i);
            return $result;
        }
        else
        {
            return false;
        }
    }

    /**
    * retrieves the number of fields in a resultset
    *
    * @return mixed If successful : number of fields in the resultset, else : FALSE
    *
    * @param int result ressource id
    *
    * @access public
    *
    * @uses query_result
    */
    function numfields($query_id = 0)
    {
        if(!$query_id)
        {
            $query_id = $this->query_result;
        }
        if($query_id)
        {
            $result = @mysql_num_fields($query_id);
            return $result;
        }
        else
        {
            return false;
        }
    }

    /**
    * retrieves name of a fields in a resultset
    *
    * @return mixed If successful : field name name, else : FALSE
    *
    * @param int result ressource id
    * @param int field index
    *
    * @access public
    *
    * @uses query_result
    */
    function fieldname($query_id = 0, $i)
    {
        if(!$query_id)
        {
            $query_id = $this->query_result;
        }
        if($query_id)
        {
            $result = @mysql_field_name($query_id, $i);
            return $result;
        }
        else
        {
            return false;
        }
    }


    /**
    * retrieves the resultset in an array
    *
    * @return mixed If successful : array containing the query resultset, else : FALSE
    *
    * @param int query id
    *
    * @access public
    *
    * @uses query_result
    */
    function getarray($query_id = 0)
    {
        if(!$query_id) $query_id = $this->query_result;

        if($query_id)
        {
            $this->array = array();

            if ($this->numrows())
            {
                $this->dataseek($query_id, 0);
                while ($fields = $this->fetchrow($query_id))
                {
                    if (sizeof($fields) == 1) $this->array[] = $fields[key($fields)];
                    else $this->array[] = $fields;
                }
            }
            return $this->array;
        }
        else return false;
    }

    function getjson($query_id = 0, $utf8 = true)
    {
        if(!$query_id) $query_id = $this->query_result;

        if($query_id)
        {
            $this->array = array();

            if ($this->numrows())
            {
                $this->dataseek($query_id, 0);
                while ($fields = $this->fetchrow($query_id))
                {
                    if ($utf8) foreach($fields as $key => $value) $fields[$key] = utf8_encode($value);

                    if (sizeof($fields) == 1) $this->array[] = $fields[key($fields)];
                    else $this->array[] = $fields;
                }
            }
            return json_encode($this->array);
        }
        else return false;
    }


    function dataseek($query_id = 0, $pos = 0)
    {
        if(!$query_id)
        {
            $query_id = $this->query_result;
        }
        if($query_id)
        {
            return @mysql_data_seek($query_id, $pos);
        }
        else return(false);

    }

    function addslashes($var)
    {
        if ($this->isconnected()) return(ploopi_array_map('mysql_real_escape_string', $var));
        else return(false);
    }

    function timer_start()
    {
        if (class_exists('timer'))
        {
            $this->db_timer = new timer();
            $this->db_timer->start();
        }
    }

    function timer_stop()
    {
        if (class_exists('timer'))
        {
            $this->exectime_queries += $this->db_timer->getexectime();
        }
    }


}
?>
