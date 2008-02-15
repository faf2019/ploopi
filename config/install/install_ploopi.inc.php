<?php
function ploopi_create_site()
{
/*  $monHeure = '-- coucou à 23:52:35 au café /n/r
  				(ou 23:51:33)';
  echo 'test = '.$monHeure. ' => '.preg_replace('#^(\-\-)(.*)(([01][0-9])|(2[0-3]))\:([0-5][0-9])\:([0-5][0-9])#','$1$2$3.$6.$7',$monHeure).'</br>'; // pour les heure dans les commentaires...
*/  
  try {
    $objInstallDBConnect = new PDO('mysql:host='.$_SESSION['install']['<DB_SERVER>'],$_SESSION['install']['<DB_LOGIN>'], $_SESSION['install']['<DB_PASSWORD>']);
    
    $objInstallRequete = $objInstallDBConnect->prepare('USE '.$_SESSION['install']['<DB_DATABASE>']);
    if(!$objInstallRequete->execute())
    {
      // database no existe
      $objInstallRequete = $objInstallDBConnect->prepare('CREATE DATABASE '.$_SESSION['install']['<DB_DATABASE>']);
      if($objInstallRequete->execute())
      {
        $objInstallRequete = $objInstallDBConnect->prepare('USE '.$_SESSION['install']['<DB_DATABASE>']);
        if(!$objInstallRequete->execute())
        {
          return false;    
        }
      }
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
      $strRequest = preg_replace('#(.*)(\-)(.*)(([01][0-9])|(2[0-3]))\:([0-5][0-9])\:([0-5][0-9])(.*)([\r\n])#','$1$2$3$4.$7.$8$9$10',$strRequest);
      if ($strRequest!='') 
      {
        $objInstallRequete = $objInstallDBConnect->prepare($strRequest);
        if(!$objInstallRequete->execute())
        {
          return false;
        }
      }
    }
    $strAdminPassword = empty($_SESSION['install']['<ADMIN_PASSWORD>']) ? 'admin' : $_SESSION['install']['<ADMIN_PASSWORD>'];
    $objInstallRequete = $objInstallDBConnect->prepare("UPDATE `ploopi_user` SET `login` = '".$_SESSION['install']['<ADMIN_LOGIN>']."', `password` = '".md5("{$_SESSION['install']['<SECRETKEY>']}/{$_SESSION['install']['<ADMIN_LOGIN>']}/".md5($strAdminPassword))."', email = '".$_SESSION['install']['<ADMIN_MAIL>']."' WHERE  `login` = 'admin'");
    $objInstallRequete->execute();
    return true;
  } 
  catch (PDOException $objInstallDBError)
  {
    echo 'erreur';
    return false;
  }
}
?>