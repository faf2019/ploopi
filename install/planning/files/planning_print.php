<?
$strPrintType = "";

// INIT PATTERN de recherche
$arrSearchPattern = array();
// Lecture session
if (isset($_SESSION['planning'][$_SESSION['ploopi']['moduleid']]['planning_request'])) $arrSearchPattern = $_SESSION['planning'][$_SESSION['ploopi']['moduleid']]['planning_request'];

//Récupération du type d'affichage
switch($arrSearchPattern['planning_display_type'])
{
    case 'month':
        $date_begin = mktime(0, 0, 0, $arrSearchPattern['planning_month'], 1, $arrSearchPattern['planning_year']);
        $date_end = mktime(0, 0, 0, $arrSearchPattern['planning_month']+1, 0, $arrSearchPattern['planning_year']);

        foreach ($ploopi_months as $intMonth => $strMonth)
		{
    		if ($arrSearchPattern['planning_month'] == $intMonth)
    		{
		        if($arrSearchPattern['planning_month'] == 4 || $arrSearchPattern['planning_month'] == 10)
		        {
		            $strPrintType = "Planning d'".$strMonth." ".$arrSearchPattern['planning_year'];     
		        }
		        else
		        {
		            $strPrintType = "Planning de ".$strMonth." ".$arrSearchPattern['planning_year'];        
		        }
       		}
        }

    break;

    case 'week':
        // On détermine les dates de la semaine courante
        $date_begin = ploopi_numweek2unixtimestamp($arrSearchPattern['planning_week'], $arrSearchPattern['planning_year']);
        $date_end = mktime(0, 0, 0, date('n', $date_begin), date('j', $date_begin)+6, date('Y', $date_begin));
        
		// Détermination du numéro de semaine max de l'année (on se positionne sur le 31/12)
		$intMaxWeek = date('W', mktime(0, 0, 0, 12, 31, $arrSearchPattern['planning_year']));

		if ($intMaxWeek == 1) $intMaxWeek = 52;
		
		$date_firstweek = ploopi_numweek2unixtimestamp(1, $arrSearchPattern['planning_year']);

		for ($intWeek = 1; $intWeek <= $intMaxWeek; $intWeek++)
		{
			// Date de début de la semaine en cours d'affichage dans la liste
			$date_week = mktime(0, 0, 0, date('n', $date_firstweek), date('j', $date_firstweek)+(($intWeek - 1) * 7), date('Y', $date_firstweek));
			if ($arrSearchPattern['planning_week'] == $intWeek)
			{
				$strPrintType = sprintf("Planning de la semaine %02d - %s", $intWeek, substr(ploopi_unixtimestamp2local($date_week),0,5));
			}
		}
    break;

    default:
    case 'today':
    case 'day':
        // On détermine la date du jour
        $date_end = $date_begin = mktime(0, 0, 0, $arrSearchPattern['planning_month'], $arrSearchPattern['planning_day'], $arrSearchPattern['planning_year']);
        
        $strPrintType = sprintf("Planning du %02d/%02d/%4d", $arrSearchPattern['planning_day'], $arrSearchPattern['planning_month'], $arrSearchPattern['planning_year']);
    break;
}

//ploopi_print_r($arrSearchPattern);

// Recherche des événements
$arrEvents = array();
    
// Recherche des événements
$arrEvents = planning_get_events(
    $arrSearchPattern['planning_resources'],
    ploopi_unixtimestamp2timestamp($date_begin),
    ploopi_unixtimestamp2timestamp(mktime(0, 0, 0, date('n', $date_end), date('j', $date_end)+1, date('Y', $date_end)))
    ); 
    
//ploopi_print_r($arrEvents);

$strDateEvent = "";
$strUserEventFirstname = "";
$strUserEventLastname = "";
$strGroupEventLabel = "";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
    	<title><? echo $strPrintType ?></title>
		<link rel="stylesheet" type="text/css" href="./modules/planning/include/styles_print.css" />
		<script type="text/javascript">window.onload = function() { window.print(); }</script>
	</head>
	<body>
        <h1><? echo $strPrintType ?></h1>
        
        <?
        foreach($arrEvents as $event)
		{
		//  ploopi_print_r($event);
		    
		    $arrEventBegin = ploopi_timestamp2local($event['timestp_begin']);
		    $arrEventEnd = ploopi_timestamp2local($event['timestp_end']);
		

		    		
		    if($arrEventBegin['date'] != $strDateEvent)
            {
		    ?>
	           <h3>
	                <?
	                    echo $arrEventBegin['date'];
	                    $strDateEvent = $arrEventBegin['date'];
	                ?>        
	            </h3>
	         <?
             }
             ?>
	        <div class="planning_print_event">
	            <span class="planning_event_who">RDV pour : 
	         <? 
	         $arrWho = array();
	         
            if(isset($event['res']['user']))
            {
            	foreach($event['res']['user'] as $id_user)
	            {
	                $objUser = new user();
	                if($objUser->open($id_user))
	                {
	                    $arrWho[] = sprintf("%s %s",$objUser->fields['firstname'],$objUser->fields['lastname']);
	                }
	            }
            }
                        
            if(isset($event['res']['group']))
            {
                foreach($event['res']['group'] as $id_group)
                {
                    $objGroup = new group();
                    if($objGroup->open($id_group))
                    {
                    	$arrWho[] = $objGroup->fields['label'];
                    }
                }
            }
            
            echo implode(', ', $arrWho);
            ?>	            
	            </span><br />
	            <span class="planning_event_time">De : <? echo substr($arrEventBegin['time'], 0, 2) ?>h<? echo substr($arrEventBegin['time'], 3, 2) ?> à <? echo substr($arrEventEnd['time'], 0, 2) ?>h<? echo substr($arrEventEnd['time'], 3, 2) ?></span>
	            <span class="planning_event_time">
	                <?
	                if($arrEventEnd['date'] != $arrEventBegin['date'])
	                {
	                    echo " le ".$arrEventEnd['date'];
	                }
	                ?>
	            </span><br />
	            <span class="planning_event_object">Objet: <? echo $event['object']; ?> </span>
	        </div>
        <?		    
		}
        ?>
	</body>
</html>