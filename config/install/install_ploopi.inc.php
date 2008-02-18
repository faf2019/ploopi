<?php
function ploopi_create_site($arrInstallInfos)
{
  try {
    $objPDO = new PDO("mysql:host={$_SESSION['install']['<DB_SERVER>']}","{$_SESSION['install']['<DB_LOGIN>']}","{$_SESSION['install']['<DB_PASSWORD>']}");
  }
  catch (PDOException $objInstallDBError)
  {
    $arrErrors = $objInstallDBError->errorInfo();
    $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2]);
    return false;
  }
  
  $intRequest = $objPDO->exec("USE `{$_SESSION['install']['<DB_DATABASE>']}`");
  if($intRequest === false)
  {
    // database no existe
    $intRequest = $objPDO->exec("CREATE DATABASE `{$_SESSION['install']['<DB_DATABASE>']}`");
    if($intRequest === false)
    {
      $arrErrors = $objPDO->errorInfo();
      $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2]);
      return false;    
    }
  }
  
  $objPDO = Null;
  try {
    $objPDO = new PDO("mysql:host={$_SESSION['install']['<DB_SERVER>']};dbname={$_SESSION['install']['<DB_DATABASE>']}","{$_SESSION['install']['<DB_LOGIN>']}", "{$_SESSION['install']['<DB_PASSWORD>']}");
  }
  catch (PDOException $objInstallDBError)
  {
    $arrErrors = $objInstallDBError->errorInfo();
    $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2]);
    return false;
  }
  
  $strModelFile = './config/config.php.model';
  $strConfigFile = './config/config.php';
  $strSqlFile = './install/system/ploopi.sql';
  $strContent = '';
  foreach($_SESSION['install'] as $strTag => $strValue)
  {
    if(substr($strTag,0,1)=='<' && substr($strTag,-1,1)=='>')
    {
      if($strTag=='<USE_DBSESSION>' || $strTag=='<URL_ENCODE>' || $strTag=='<FRONTOFFICE>' || $strTag=='<REWRITERULE>')
      {
        $strValue = ($strValue) ? 'true' : 'false';
      }
      $arrTags[] =  $strTag;
      $arrReplace[] = $strValue;
    }
  }

  $f = fopen( $strModelFile, "r" );
  while (!feof($f)) $strContent .= fgets($f, 4096);
  fclose($f);
  
  $strContent = str_replace($arrTags, $arrReplace, $strContent);

  $fc = fopen( $strConfigFile, "w" );
  fwrite($fc, $strContent);
  fclose($fc);

  $arrRequests = array();
  $strSql = '';

  $fs = fopen ($strSqlFile, "r");
  while (!feof($fs))
  {
    $strSql .= fgets($fs, 4096);
  }
  fclose ($fs);

  $strSql = trim($strSql);
  
  $arrRequests = explode(";\n",$strSql);
  
  foreach ($arrRequests AS $strKey => $strRequest)
  {
    $strRequest = trim($strRequest);
    $strRequest = preg_replace('#(.*)(\-)(.*)(([01][0-9])|(2[0-3]))\:([0-5][0-9])\:([0-5][0-9])(.*)(([\r\n])|($))#','$1$2$3$4.$7.$8$9$10',$strRequest);
    if ($strRequest!='') 
    {
      $intRequest = $objPDO->exec("$strRequest");
      if($intRequest === false)
      {
        $arrErrors = $objPDO->errorInfo();
        $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2].'<br/>'.$strRequest);
        return false;
      }
      $intRequest = null;
    }
  }
  $strAdminPassword = empty($_SESSION['install']['<ADMIN_PASSWORD>']) ? 'admin' : $_SESSION['install']['<ADMIN_PASSWORD>'];
  $intRequest = $objPDO->exec("UPDATE `ploopi_user` SET `login` = '{$_SESSION['install']['<ADMIN_LOGIN>']}', `password` = '".md5("{$_SESSION['install']['<SECRETKEY>']}/{$_SESSION['install']['<ADMIN_LOGIN>']}/".md5($strAdminPassword))."', email = '{$_SESSION['install']['<ADMIN_MAIL>']}' WHERE  `login` = 'admin'");
  if($intRequest === false)
  {
    $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2]);
    return false;
  }
  else
  {
    return true;
  }
}
?>