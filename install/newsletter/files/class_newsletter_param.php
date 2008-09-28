<?php
/*
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
 * Gestion des newsletters
 *
 * @package newsletter
 * @subpackage param
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_newsletter_send
 *
 * @package newsletter
 * @subpackage param
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

class newsletter_param extends data_object
{   
  /**
   * Constructeur de la classe
   *
   * @return data_object
   */
  function newsletter_param()
  {
    parent::data_object('ploopi_mod_newsletter_param','id_module','param');
  }
  
  /**
   * Recupere des params pour l'envoi et les convert de "contant"
   *
   * @param int $id_module
   * @return array('host' => '...','from_name' => '...','from_email' => '...','send_by' => '...')
   */
  function get_param($id_module = 0)
  {
    $arrParam = array('host' => 'http://'.$_SERVER['HTTP_HOST'].'/',
                      'from_name' => '',
                      'from_email' => '',
                      'send_by' => '0');
    
    global $db;
    
    if(empty($id_module)) $id_module = $_SESSION['ploopi']['moduleid'];
    
    $sql = "SELECT * FROM ploopi_mod_newsletter_param WHERE id_module = {$id_module}";
    $resultSql = $db->query($sql);
    
    while ($fields = $db->fetchrow($resultSql))
    {
      $arrParam[$fields['param']] = $fields['value'];
    }
    
    return $arrParam; 
  }
  
  /**
   * Sauvegarde un param général
   * 
   * @return mixed valeur de la clé primaire
   */
  function save()
  {
    if($this->fields['param'] == 'host')
    {
      if(empty($this->fields['value'])) $this->fields['value'] = 'http://'.$_SERVER['HTTP_HOST'].'/';
      if(substr($this->fields['value'],0,7) != 'http://') $this->fields['value'] = 'http://'.$this->fields['value'];
      if(substr($this->fields['value'],-1) != '/') $this->fields['value'] = $this->fields['value'].'/';
    }
    
    $this->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
    
    return parent::save();
  }
  
}
?>