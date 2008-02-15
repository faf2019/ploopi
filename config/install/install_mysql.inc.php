<?php
function ploopi_Test_Database($arrInstallInfos,$intInstallInfos,$arrInstallRequestDB)
{
  try {
    $objInstallDBConnect = new PDO('mysql:host='.$_SESSION['install']['<DB_SERVER>'],$_SESSION['install']['<DB_LOGIN>'], $_SESSION['install']['<DB_PASSWORD>']);
    $arrInstallInfos[$intInstallInfos]['mess_replace'] = array(_PLOOPI_INSTALL_REQUIRED.$arrInstallRequestDB[$_SESSION['install']['<DB_TYPE>']]['version'],
                                                               _PLOOPI_INSTALL_INSTALLED.$objInstallDBConnect->getAttribute(PDO::ATTR_SERVER_VERSION));
    $arrInstallInfos[$intInstallInfos]['form'][] = array('label' => _PLOOPI_INSTALL_DB_DATABASE_NAME,
                                                         'input' => '<input name="db_database_name" id="db_database_name" type="text" value="'.$_SESSION['install']['<DB_DATABASE>'].'"/>'
                                                        );
    if(!version_compare($objInstallDBConnect->getAttribute(PDO::ATTR_SERVER_VERSION),$arrInstallRequestDB[$_SESSION['install']['<DB_TYPE>']]['version'],'>='))
    {
      $arrInstallInfos[$intInstallInfos]['state'] = false;
      return false;
    }
    else // Database Version is OK
    {
      // List of databases (if we can)
      $objRequete = $objInstallDBConnect->prepare('SHOW DATABASES');
      if($objRequete->execute())
      {
        $strInstallListDbName = '';
        while ($arrInstalledDB = $objRequete->fetch())
        {
          if($_SESSION['install']['<DB_TYPE>'] !== 'mysql' ||
              ($_SESSION['install']['<DB_TYPE>'] === 'mysql' && ($arrInstalledDB['Database'] !== 'information_schema' && $arrInstalledDB['Database'] !== 'mysql'))) // Database Reserved by mysql
          {
            if($arrInstalledDB['Database'] === $_SESSION['install']['<DB_DATABASE>'])
            {
              $strInstallSelected = 'selected';
              $booFindDbListe = true;
            }
            else
            {
              $strInstallSelected = '';
            }
            $strInstallListDbName .= '<option value="'.$arrInstalledDB['Database'].'" '.$strInstallSelected.'>'.$arrInstalledDB['Database'].'</option>';
          }
        }
        if($strInstallListDbName != '')
        {
          $arrInstallInfos[$intInstallInfos]['form'][] = array('label' => _PLOOPI_INSTALL_DB_DATABASE_SELECT,
                                                               'input' => '<select name="db_database_select" id="db_database_select" onChange="javascript:duplicSelectToField(this,\'db_database_name\');">'.
          					                                            '<option value="">'._PLOOPI_INSTALL_DB_DATABASE_SELECT_NEW.'</option>'.
                                                                          $strInstallListDbName.
                                                                          '</select>');
        }
        
        if($_SESSION['install']['<DB_DATABASE>'] !== '')
        {
          if(!$booFindDbListe) // The database NO exist
          {
            // CREATE DATABASE 
            $objInstallRequete = $objInstallDBConnect->prepare('CREATE DATABASE '.$_SESSION['install']['<DB_DATABASE>']);
            $booInstallState = ($objInstallRequete->execute()) ? true : false;
            
            $arrInstallInfos[] = array('id' => 'div_db_create_db', 
                                       'state' => $booInstallState,
                                       'title' => '_PLOOPI_INSTALL_DATA_BASE_CREATE_DB', 
                                       'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                       'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
          }
          // USE
          $objInstallRequete = $objInstallDBConnect->prepare('USE '.$_SESSION['install']['<DB_DATABASE>']);
          $arrInstallInfos[] = array('id' => 'div_db_use', 
                                     'state' => $objInstallRequete->execute(),
                                     'title' => '_PLOOPI_INSTALL_DATA_BASE_USE',
                                     'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                     'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
  
          // Test if it's not a ploopi database
          $objInstallRequete = $objInstallDBConnect->prepare('SELECT * FROM ploopi_user');
          if($objInstallRequete->execute())
          {
            if($_SESSION['install']['replace_database'])
            {
              $strInstallCheckedYes = 'checked';$strInstallCheckedNo = '';
            }
            else
            {
              $strInstallCheckedYes = '';$strInstallCheckedNo = 'checked';
            }
            
            $arrInstallInfos[] = array('id' => 'div_db_use', 
                                       'state' => $_SESSION['install']['replace_database'],
                                       'title' => '_PLOOPI_INSTALL_DATA_BASE_PLOOPI_EXIST',
                                       'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                       'form'  => array(array('label' => _PLOOPI_INSTALL_DATA_BASE_PLOOPI_EXIST_FIELD,
                                        			      'input' => _PLOOPI_INSTALL_YES.': <INPUT type="radio" name="del_exist" value="1" '.$strInstallCheckedYes.'> '.
                                                                   _PLOOPI_INSTALL_NO.': <INPUT type="radio" name="del_exist" value="0" '.$strInstallCheckedNo.'>'))
                                      );
          }
          // CREATE TABLE
          $objInstallRequete = $objInstallDBConnect->prepare('CREATE TABLE ploopi_install_test (id int NULL)');
          $arrInstallInfos[] = array('id' => 'div_db_create', 
                                     'state' => $objInstallRequete->execute(),
                                     'title' => '_PLOOPI_INSTALL_DATA_BASE_CREATE',
                                     'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                     'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
          // INSERT
          $objInstallRequete = $objInstallDBConnect->prepare('INSERT INTO ploopi_install_test (id) VALUES (1)');
          $arrInstallInfos[] = array('id' => 'div_db_insert', 
                                     'state' => $objInstallRequete->execute(),
                                     'title' => '_PLOOPI_INSTALL_DATA_BASE_INSERT',
                                     'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                     'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
          // SELECT
          $objInstallRequete = $objInstallDBConnect->prepare('SELECT * FROM ploopi_install_test');
          $arrInstallInfos[] = array('id' => 'div_db_select', 
                                     'state' => $objInstallRequete->execute(),
                                     'title' => '_PLOOPI_INSTALL_DATA_BASE_SELECT',
                                     'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                     'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
          // UPDATE
          $objInstallRequete = $objInstallDBConnect->prepare('UPDATE ploopi_install_test SET id = 2');
          $arrInstallInfos[] = array('id' => 'div_db_update', 
                                     'state' => $objInstallRequete->execute(),
                                     'title' => '_PLOOPI_INSTALL_DATA_BASE_UPDATE',
                                     'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                     'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
          // DELETE
          $objInstallRequete = $objInstallDBConnect->prepare('DELETE FROM ploopi_install_test');
          $arrInstallInfos[] = array('id' => 'div_db_delete', 
                                     'state' => $objInstallRequete->execute(),
                                     'title' => '_PLOOPI_INSTALL_DATA_BASE_DELETE',
                                     'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                     'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
          // DROP TABLE
          $objInstallRequete = $objInstallDBConnect->prepare('DROP TABLE ploopi_install_test');
          $arrInstallInfos[] = array('id' => 'div_db_drop', 
                                     'state' => $objInstallRequete->execute(),
                                     'title' => '_PLOOPI_INSTALL_DATA_BASE_DROP',
                                     'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                     'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
          if(!$booFindDbListe) // The database NO exist DROP after test
          {
            // DROP DATABASE
            $objInstallRequete = $objInstallDBConnect->prepare('DROP DATABASE '.$_SESSION['install']['<DB_DATABASE>']);
            $arrInstallInfos[] = array('id' => 'div_db_drop_db', 
                                       'state' => $objInstallRequete->execute(),
                                       'title' => '_PLOOPI_INSTALL_DATA_BASE_DROP_DB',
                                       'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                       'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
          }
        }
        else
        {
          if(isset($_POST['db_database_name']))
          {
            $arrInstallInfos[$intInstallInfos]['state'] = false;
            $arrInstallInfos[$intInstallInfos]['warn_replace'] = array(_PLOOPI_INSTALL_DB_ERR_NAME_DB);
          }
          return false;
        }
      }
    }
  }
  catch (PDOException $objInstallDBError) {
    $arrInstallInfos[$intInstallInfos]['state'] = false;
    $arrInstallInfos[$intInstallInfos]['mess_replace'] = array(_PLOOPI_INSTALL_REQUIRED.$arrInstallRequestDB[$_SESSION['install']['<DB_TYPE>']]['version'],
                                                               _PLOOPI_INSTALL_INSTALLED.'?');
    $arrInstallInfos[$intInstallInfos]['warn_replace'] = array(_PLOOPI_INSTALL_DB_ERR_CONNECT);
    return false;
    
  }
  return true;
}
?>