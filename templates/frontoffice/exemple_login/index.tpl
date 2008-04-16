<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION} {PAGE_DESCRIPTION}" />
    <meta name="keywords" content="{PAGE_ALLKEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />

    <title>{WORKSPACE_TITLE} - {PAGE_TITLE}</title>

    <link rel="alternate" type="application/rss+xml" href="{SITE_RSSFEED_URL}" title="{SITE_RSSFEED_TITLE}">
    <link rel="alternate" type="application/rss+xml" href="{HEADING_RSSFEED_URL}" title="{HEADING_RSSFEED_TITLE}">

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/forms.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/rss.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/news.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/search.css" media="screen" />

    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/png.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles_ie.css" media="screen" />
    <![endif]-->

    <!-- BEGIN ploopi_js -->
        <script type="text/javascript" src="{ploopi_js.PATH}"></script>
    <!-- END ploopi_js -->
    
    <script type="text/javascript">
    //<!--
    {ADDITIONAL_JAVASCRIPT}
    //-->
    </script>

</head>

<body>
    <div id="wrap">
        <div id="header">
            <div id="title">
                <div id="login">
                    <form action="admin.php" method="post">
                        <label>Utilisateur:</label>
                        <input type="text" value="" name="ploopi_login" />
                        <label>Mot de Passe:</label>
                        <input type="password" value="" name="ploopi_password" />
                        <input type="submit" class="button" value="&raquo; Connexion" />
                    </form>
                </div>
                {SITE_TITLE}
            </div>
            <div id="path">
                <a href="./">&raquo;&nbsp;Accueil</a>
                <!-- BEGIN switch_heading1 -->
                <a href="{switch_heading1.LINK}">&raquo;&nbsp;{switch_heading1.LABEL}</a>
                <!-- END switch_heading1 -->
                <!-- BEGIN switch_heading2 -->
                <a href="{switch_heading2.LINK}">&raquo;&nbsp;{switch_heading2.LABEL}</a>
                <!-- END switch_heading2 -->
                <!-- BEGIN switch_heading3 -->
                <a href="{switch_heading3.LINK}">&raquo;&nbsp;{switch_heading3.LABEL}</a>
                <!-- END switch_heading3 -->
                <!-- BEGIN switch_heading4 -->
                <a href="{switch_heading4.LINK}">&raquo;&nbsp;{switch_heading4.LABEL}</a>
                <!-- END switch_heading4 -->
                <!-- BEGIN switch_heading5 -->
                <a href="{switch_heading5.LINK}">&raquo;&nbsp;{switch_heading5.LABEL}</a>
                <!-- END switch_heading5 -->
    
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
                        <a class="rssfeedtitle" href="{rssfeed.LINK}">{rssfeed.TITLE}<br /><i>{rssfeed.SUBTITLE}</i></a>
                        <!-- BEGIN rssentry -->
                            <a title="{rssfeed.rssentry.TITLE}" class="rsscache" href="{rssfeed.rssentry.LINK}" target="_blank">
                                <div><b>{rssfeed.rssentry.TITLE}</b></div>
                                <div><i>{rssfeed.rssentry.SUBTITLE}</i></div>
                                <div style="font-size:0.8em;margin-top:2px;">{rssfeed.rssentry.PUBLISHED_DATE} {rssfeed.rssentry.PUBLISHED_TIME}</div>
                            </a>
                        <!-- END rssentry -->
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
                    <!-- BEGIN switch_search -->
                    <h2>Résultat de la recherche pour "{PAGE_QUERYSTRING}"</h2>
                    <div id="search_result">
                        <!-- BEGIN result -->
                            <a href="{switch_search.result.LINK}" title="Lien vers {switch_search.result.TITLE}">
                                <h2>{switch_search.result.TITLE}<span class="relevance">{switch_search.result.RELEVANCE} %</span></h2>
                                <div class="extract">{switch_search.result.EXTRACT}</div>
                                <div class="link">&raquo; {switch_search.result.LINK} ({switch_search.result.SIZE} ko)</div>
                            </a>
                        <!-- END result -->
                    </div>
                    <!-- END switch_search -->

                    <!-- BEGIN switch_content_page -->
                        <h2>{PAGE_TITLE}</h2>
                        {PAGE_CONTENT}
                    <!-- END switch_content_page -->
                    
                    <!-- BEGIN switch_content_heading -->
                        <!-- BEGIN subheading1 -->
                            <a class="subheading1" href="{switch_content_heading.subheading1.LINK}">
                                <img src="{TEMPLATE_PATH}/gfx/folder.png" />
                                <span>{switch_content_heading.subheading1.LABEL}</span>
                            </a>
                            <div id="subheading2">
                            <!-- BEGIN subheading2 -->
                                &raquo;&nbsp;<a href="{switch_content_heading.subheading1.subheading2.LINK}">{switch_content_heading.subheading1.subheading2.LABEL}</a>&nbsp;&nbsp; 
                            <!-- END subheading2 -->
                            </div>
                            
                        <!-- END subheading1 -->
                    <!-- END switch_content_heading -->
                    
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
