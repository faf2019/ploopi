<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION} {PAGE_META_DESCRIPTION}" />
    <meta name="keywords" content="{WORKSPACE_META_KEYWORDS}, {PAGE_META_KEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/styles.css" title="andreas07" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/skin.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/forms.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/calendar.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/search.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/system_trombi.css" media="screen,projection" />
    
    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/skin_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar_ie.css" media="screen" />
    <![endif]-->

    <!-- BEGIN ploopi_js -->
    <script type="text/javascript" src="{ploopi_js.PATH}"></script>
    <!-- END ploopi_js -->
    
    <!-- BEGIN module_js -->
    <script type="text/javascript" src="{module_js.PATH}"></script>
    <!-- END module_js -->
        
    <script type="text/javascript">
    //<!--
    {ADDITIONAL_JAVASCRIPT}
    //-->
    </script>
    <!-- BEGIN switch_content_page -->
    {PAGE_HEADCONTENT}
    <!-- END switch_content_page -->
        
	<title>{SITE_TITLE} - {PAGE_TITLE}</title>
</head>

<body>
<div id="wrap">

<div id="leftside">
	<h1>{SITE_TITLE}</h1>
	<h2>Slogan à modifier</h2>

	<div id="menu">
		<!-- BEGIN root1 -->
		    <!-- BEGIN heading1 -->
		    <a class="{root1.heading1.SEL}" href="{root1.heading1.LINK}" target="{root1.heading1.LINK_TARGET}" href="index.html">{root1.heading1.LABEL}</a>
		    <!-- END heading1 -->
		<!-- END root1 -->
	</div>
	
	<h3>Dernière mise à jour:</h3>
	<p>{LASTUPDATE_DATE} à {LASTUPDATE_TIME}</p>
</div>

<div id="extras">
    <h2>Recherche</h2>
    <form method="post" action="{SITE_HOME}">
	    <input type="text" alt="recherche" id="recherche_field" name="query_string" value="{PAGE_QUERYSTRING}" class="text" style="width:75%" />
	    <input type="submit" value="go" class="button" style="width:20%" />
    </form>
	
	<!-- BEGIN switch_subscription -->
        <h2>Abonnement</h2>
		   <!-- BEGIN switch_response -->
		   <div style="padding-bottom:4px;"><strong>{switch_subscription.switch_response.CONTENT}</strong></div>
		   <!-- END switch_response -->
		    <form method="post" action="{switch_subscription.ACTION}">
                <div>
		            <input type="radio" class="pointer" name="subscription_headingid" id="subscription_site" value="{switch_subscription.ROOTID}" /><label class="pointer" for="subscription_site">Site</label>
		            <input type="radio" class="pointer" name="subscription_headingid" id="subscription_heading" value="{switch_subscription.HEADINGID}" checked /><label class="pointer" for="subscription_heading">Rubrique</label>
		        </div>
		        <div>
		            <input type="text" title="Entrez votre adresse email" alt="Entrez votre adresse email" class="text" name="subscription_email" value="Entrez votre adresse email" onfocus="javascript:this.value='';" style="width:75%" />
		            <input type="submit" title="Bouton pour valider l'abonnement" class="button" value="go" style="width:20%" />
		        </div>
		    </form>
	<!-- END switch_subscription -->

	<!-- BEGIN switch_pages -->
	<ul>
        <h2>Articles</h2>
        <!-- BEGIN page -->
	    <li><a class="{switch_pages.page.SEL}" href="{switch_pages.page.LINK}">{switch_pages.page.LABEL}</a></li>
        <!-- END page -->
	</ul>
	<!-- END switch_pages -->
	
    <h2>Nuage de tags</h2>
	<div id="tagcloud">
	    <!-- BEGIN tagcloud -->
	        <a href="{tagcloud.LINK}" class="{tagcloud.SELECTED}" title="Afficher les articles contenant le tag &laquo; {tagcloud.TAG} &raquo;" style="font-size:{tagcloud.SIZE}%;">{tagcloud.TAG}<sup>{tagcloud.OCCURENCE}</sup></a>
	    <!-- END tagcloud -->
	</div>   
</div>

<div id="content">
    <!-- BEGIN switch_content_page -->
		<h1>{PAGE_TITLE}</h1>
		<h2>{PAGE_DESCRIPTION}</h2>
	    <p>{PAGE_CONTENT}</p>
    <!-- END switch_content_page -->
    
    <!-- BEGIN switch_content_message -->
        <h1>{MESSAGE_TITLE}</h1>
        <h2>{MESSAGE_CONTENT}</h2>
    <!-- END switch_content_message -->  
      
    <!-- BEGIN switch_content_error -->
	    <h1>Erreur {PAGE_ERROR_CODE}</h1>
	    <h2>Cette page n'existe pas</h2>
    <!-- END switch_content_error -->
    
    <!-- BEGIN switch_search -->
	    <h1>Résultat de la recherche</h1>
	    <h2>Mot clé: {PAGE_QUERYSTRING}</h2>
        <!-- BEGIN switch_notfound -->
            <p>Aucun résultat pour cette recherche</p>
        <!-- END switch_notfound -->
	    <div id="search_result">
	        <!-- BEGIN result -->
	            <a href="{switch_search.result.LINK}" title="Lien vers {switch_search.result.TITLE}">
	                <h3>{switch_search.result.TITLE}<span class="relevance">{switch_search.result.RELEVANCE} %</span></h3>
	                {switch_search.result.EXTRACT}
	                <div class="link">&raquo; {switch_search.result.SHORT_LINK} ({switch_search.result.SIZE} ko)</div>
	            </a>
	        <!-- END result -->
	    </div>
    <!-- END switch_search -->

    <!-- BEGIN switch_tagsearch -->
	    <h1>Articles tagués</h1>
	    <h2>{PAGE_QUERYTAG}</h2>
	    <div id="search_result">
	        <!-- BEGIN result -->
	            <a href="{switch_tagsearch.result.LINK}" title="Lien vers {switch_tagsearch.result.TITLE}">
	                <h3>{switch_tagsearch.result.TITLE}</h3>
	                <div class="link">&raquo; {switch_tagsearch.result.SHORT_LINK} ({switch_tagsearch.result.SIZE} ko)</div>
	            </a>
	        <!-- END result -->
	    </div>
    <!-- END switch_tagsearch -->    
    
	<!-- BEGIN switch_content_heading -->
        <h1>{HEADING_LABEL}</h1>
	    <!-- BEGIN page -->
	        <a class="headings_page" href="{switch_content_heading.page.LINK}">
	            <img src="{TEMPLATE_PATH}/img/page.png" />
	            <span><b>{switch_content_heading.page.LABEL}</b><br />{switch_content_heading.page.DATE}</span>
	        </a>
	    <!-- END page -->
	
	    <!-- BEGIN subheading1 -->
	        <a class="subheading1" href="{switch_content_heading.subheading1.LINK}">
	            <img src="{TEMPLATE_PATH}/img/folder.png" />
	            <span>{switch_content_heading.subheading1.LABEL}</span>
	        </a>
	        <div id="subheading2">
	        <!-- BEGIN subheading2 -->
	            &raquo;&nbsp;<a href="{switch_content_heading.subheading1.subheading2.LINK}">{switch_content_heading.subheading1.subheading2.LABEL}</a>&nbsp;&nbsp; 
	        <!-- END subheading2 -->
	        </div>
	    <!-- END subheading1 -->
	<!-- END switch_content_heading -->    
    
    <!-- BEGIN switch_content_sitemap -->
        <h1>Plan du site</h1>
        <div id="sitemap">
            <!-- BEGIN heading0 -->
            <div>
            <a href="{switch_content_sitemap.heading0.LINK}">{switch_content_sitemap.heading0.LABEL}</a>
            <!-- BEGIN heading1 -->
            <div>
            <a href="{switch_content_sitemap.heading0.heading1.LINK}">{switch_content_sitemap.heading0.heading1.LABEL}</a>
            <!-- BEGIN heading2 -->
            <div>
            <a href="{switch_content_sitemap.heading0.heading1.heading2.LINK}">{switch_content_sitemap.heading0.heading1.heading2.LABEL}</a>
            <!-- BEGIN heading3 -->
            <div>
            <a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.LABEL}</a>
            <!-- BEGIN heading4 -->
            <div>
            <a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LABEL}</a>
            </div>
            <!-- END heading4 -->
            </div>
            <!-- END heading3 -->
            </div>
            <!-- END heading2 -->
            </div>
            <!-- END heading1 -->
            </div>
            <!-- END heading0 -->
        </div>
    <!-- END switch_content_sitemap -->
    
          
	<h3>Informations sur le site</h3>
    <p>&copy; 2008 <a href="#">{WORKSPACE_META_COPYRIGHT}</a> | Original design by <a href="http://andreasviklund.com/">Andreas Viklund</a> | Propulsé par <a href="http://www.ploopi.org" target="_blank">Ploopi</a></p>
</div>

</div>
</body>
</html>