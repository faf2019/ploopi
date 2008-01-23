<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
	<meta name="description" content="{WORKSPACE_META_DESCRITPION} {PAGE_DESCRIPTION}" />
	<meta name="keywords" content="{PAGE_ALLKEYWORDS}" />
	<meta name="author" content="{WORKSPACE_META_AUTHOR}" />
	<meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
	<meta name="robots" content="{WORKSPACE_META_ROBOTS}" />


	<link rel="icon" href="{TEMPLATE_PATH}/gfx/favicon.png" type="image/png" />
	<link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/styles.css" media="screen" title="styles" />
	<link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/rss.css" media="screen" title="styles" />
	<link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/agenda.css" media="screen" title="styles" />
	<link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/forms.css" media="screen" title="styles" />
	<link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/news.css" media="screen" title="styles" />

	<!--[if lte IE 7]>
	<link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/styles_ie.css" media="screen" title="styles" />
	<![endif]-->
	
	<script type="text/javascript" src="./include/prototype.js"></script>
	<script type="text/javascript" src="./include/functions.js"></script>
	<script type="text/javascript">
	//<!--
	{ADDITIONAL_JAVASCRIPT}

	var lstmsg = new Array();
	lstmsg[0] = "L'adresse mèl n'est pas valide.\nIl n'y a pas de caractère @\nUne adresse mèl valide est du type \"adresse@domaine.com\"";
	lstmsg[1] = "L'adresse mèl n'est pas valide.\nIl ne peut pas y avoir un point (.) juste après @\nUne adresse mèl valide est du type \"adresse@domaine.com\"";
	lstmsg[2] = "L'adresse mèl n'est pas valide.\nL'adresse mèl ne peut pas finir par un point (.)\nUne adresse mèl valide est du type \"adresse@domaine.com\"";
	lstmsg[3] = "L'adresse mèl n'est pas valide.\nL'adresse mèl ne peut pas contenir 2 points (.) qui se suivent.\nUne adresse mèl valide est du type \"adresse@domaine.com\"";
	lstmsg[4] = "Le champ '<FIELD_LABEL>' ne doit pas être vide";
	lstmsg[5] = "Le champ '<FIELD_LABEL>' doit être un nombre entier valide";
	lstmsg[6] = "Le champ '<FIELD_LABEL>' doit être un nombre réel valide";
	lstmsg[7] = "Le champ '<FIELD_LABEL>' doit être une date valide";
	lstmsg[8] = "Le champ '<FIELD_LABEL>' doit être une heure valide";
	lstmsg[9] = "Vous devez sélectionner une valeur pour le champ '<FIELD_LABEL>'";
	lstmsg[10] = "Le champ '<FIELD_LABEL>' doit être une couleur valide (#ffff00 / jaune / yellow)";

	var error_bgcolor = "#FFAAAA";

	//-->
	</script>


	<title>{SITE_TITLE} - {PAGE_TITLE} - {HEADING1_TITLE}</title>
</head>

<body>
	<div id="wrap">
		<div id="header">
			<div id="title">{SITE_TITLE}</div>
			<div id="path">
				<!-- BEGIN path -->
					<a href="{path.LINK}">&raquo;&nbsp;{path.LABEL}</a>
				<!-- END path -->
				<!-- BEGIN switch_content_page -->
					<a href="#">&raquo;&nbsp;{PAGE_TITLE}</a>
				<!-- END switch_content_page -->
			</div>
			<div id="pathtrans">
			</div>
		</div>


		<div id="main">
			<div id="mainmenu">
				<div id="hmenu">
					<div style="margin-bottom:4px;">
						<label for="recherche_field">Recherche:</label>
						<form name="form_search" method="post" action="index.php">
						<input type="text" alt="recherche" id="recherche_field" name="query_string" value="{PAGE_QUERYSTRING}" style="width:70%;margin-right:2px;border:1px solid #c0c0c0;padding-left:2px;font-size:10px;"><input type="submit" value="go" style="width:20%;border:1px solid #a0a0a0;background-color:#e0e0e0;font-size:10px;">
						</form>
					</div>
				
					<!-- BEGIN root1 -->
						<!-- BEGIN heading1 -->
						<a class="r1h1{root1.heading1.SEL}" href="{root1.heading1.LINK}" {root1.heading1.LINK_TARGET}>{root1.heading1.LABEL}</a>
						<!-- END heading1 -->
					<!-- END root1 -->

					<!-- BEGIN rssfeed -->
					<div class="rssfeed">
						<a class="rssfeedtitle" href="{rssfeed.LINK}">{rssfeed.TITLE}</a>
						<!-- BEGIN rsscache -->
							<a class="rsscache" href="{rssfeed.rsscache.LINK}" target="_blank">
								<div>{rssfeed.rsscache.TITLE}</div>
								<div style="font-size:0.8em;margin-top:2px;">{rssfeed.rsscache.DATE} // {rssfeed.rsscache.TIME}</div>
							</a>
						<!-- END rsscache -->
					</div>
					<!-- END rssfeed -->
				</div>
			</div>

			<div id="content">
				
				<div id="pagemenu">
					<!-- BEGIN page -->
					<a class="page{page.SEL}" href="{page.LINK}">{page.LABEL}</a>
					<!-- END page -->
				</div>

				<div id="pagecontent">
					<!-- BEGIN switch_content_page -->
					<h2>{PAGE_TITLE}</h2>
					{PAGE_CONTENT}
					<!-- END switch_content_page -->
					
					<!-- BEGIN switch_content_phpdig -->
					<h2>Résultat de la recherche</h2>
					{PHPDIG_RESULT}
					<!-- END switch_content_phpdig -->
				</div>
			</div>
		</div>

		<div id="footer">
			Designed by <a href="http://andreasviklund.com">Andreas Viklund</a> & <a href="http://netlor.fr">netlor</a> // Powered by <a href="http://www.ploopi.fr">PLOOPI</a> // <a href="admin.php">Online Management</a>
			<br />[ time: <PLOOPI_EXEC_TIME> ms | php: <PLOOPI_PHP_P100>% | sql: <PLOOPI_NUMQUERIES>q | size: <PLOOPI_PAGE_SIZE>kB ]
		</div>

	</div>
</body>
</html>
