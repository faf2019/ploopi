<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- Réalisation: Ovensia 2007 // Template pour PLOOPI -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="{WORKSPACE_META_DESCRITPION} {PAGE_DESCRIPTION}" />
    <meta name="keywords" content="{PAGE_ALLKEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />

    <title>{WORKSPACE_TITLE} - {PAGE_TITLE}</title>

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/forms.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/rss.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/news.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/search.css" media="screen" />

    <!--[if lte IE 7]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar_ie.css" media="screen" />
    <![endif]-->
    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/png.css" media="screen" />
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
    <div id="entete">
        <a href="./" title="Lien vers Accueil" accesskey="1" tabindex="1">Accueil</a>&nbsp;&#149;
        <a href="#page_content" title="Lien vers le Contenu" accesskey="2" tabindex="2">Aller au Contenu</a>&nbsp;&#149;
        <a href="#recherche" title="Lien vers le Moteur de Recherche" accesskey="C" tabindex="3">Aller au Moteur de recherche</a>&nbsp;&nbsp;
    </div>
    <div id="page">
        <div id="page_haut"></div>
        <div id="page_inner">
            <div id="page_menu">
                <!-- BEGIN root1 -->
                    <!-- BEGIN heading1 -->
                        <span>|</span>
                        <a href="{root1.heading1.LINK}" class="{root1.heading1.SEL}" title="Lien vers {root1.heading1.LABEL}">{root1.heading1.LABEL}</a>
                    <!-- END heading1 -->
                <!-- END root1 -->
                <span>|</span>
            </div>
            <div id="title">
                <div id="maintitletrans">{WORKSPACE_TITLE}</div>
                <div id="maintitle">{WORKSPACE_TITLE}</div>
                <div id="subtitle">{HEADING0_DESCRIPTION}</div>
            </div>

            <div id="pages">
                <!-- BEGIN page -->
                    <a class="{page.SEL}" title="Lien vers la page {page.LABEL}" href="{page.LINK}">{page.LABEL}</a>
                    <!-- BEGIN sw_separator -->
                    &nbsp;&#149;&nbsp;
                    <!-- END sw_separator -->
                <!-- END page -->
                &nbsp;
            </div>

            <div id="path">
                <div id="lastupdate">
                    Dernière mise à jour le {LASTUPDATE_DATE} à {LASTUPDATE_TIME}
                </div>
                Vous êtes ici :
                <!-- BEGIN path -->
                    &raquo;&nbsp;<a href="{path.LINK}">{path.LABEL}</a>
                <!-- END path -->
                <!-- BEGIN switch_content_page -->
                    &raquo;&nbsp;<a href="#">{PAGE_TITLE}</a>
                <!-- END switch_content_page -->
            </div>
            <div id="page_main">
                <div id="menu_vertical">
                    <div id="recherche">
                        <form method="post" action="index.php">
                            <fieldset>
                                <label for="recherche_field">Recherche:</label>
                                <input type="text" title="Champ de recherche" alt="Champ de recherche" id="recherche_field" name="query_string" value="{PAGE_QUERYSTRING}" onfocus="javascript:this.value='';" />
                                <input type="submit" title="Bouton pour valider la recherche" id="recherche_button" value="go" />
                            </fieldset>
                        </form>
                    </div>
                    <div id="actubox">
                        <h1>Actualités</h1>
                        <marquee behavior="scroll" direction="up" width="100%" scrollamount="2"  scrolldelay="70" onmouseover="javascript:this.stop();" onmouseout="javascript:this.start();">
                        <!-- BEGIN news -->
                            <div><strong>{news.TITLE}</strong></div>
                            <div class="news_date">le {news.DATE} à {news.TIME}</div>
                            <div class="news_content">{news.CONTENT}</div>
                            <div style="padding-bottom:4px;"><a href="{news.URL}" target="_blank">{news.URLTITLE}</a></div>
                        <!-- END news -->
                        </marquee>
                    </div>
                </div>

                <div id="page_content">
                    <!-- BEGIN switch_search -->
                    <h1>Résultat de la recherche pour "{PAGE_QUERYSTRING}"</h1>
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
                        <h1>{PAGE_TITLE}</h1>
                        {PAGE_CONTENT}
                        <div id="page_lastupdate">Dernière modification le {PAGE_LASTUPDATE_DATE} à {PAGE_LASTUPDATE_TIME}</div>
                    <!-- END switch_content_page -->
                </div>
            </div>
            <div id="menubas">
                <!-- BEGIN root2 -->
                    <!-- BEGIN heading1 -->
                        <span>|</span>
                        <a href="{root2.heading1.LINK}" title="Lien vers {root2.heading1.LABEL}">{root2.heading1.LABEL}</a>
                    <!-- END heading1 -->
                <!-- END root2 -->
                <span>|</span><a href="#entete" title="Lien vers Haut de page">Haut de page</a><span>|</span>
            </div>
        </div>
        <div id="page_bas"></div>
    </div>
    <div id="pied">
    Réalisation <a href="http://www.ovensia.fr" title="Aller sur le site ovensia.fr"><strong>Ovensia</strong></a> &#149; Template <a href="http://www.ovensia.fr" title="Aller sur le site ovensia.fr"><strong>Ovensia</strong></a> &#149; Propulsé par <strong>PLOOPI</strong>&nbsp;&nbsp;
    <div id="execinfo">
        Ce site est valide <a href="http://validator.w3.org/check?uri=referer" title="Vérifier la validité XHTML 1.0 strict"><strong>XHTML 1.0 strict</strong></a>, <a href="http://jigsaw.w3.org/css-validator/check/referer" title="Vérifier la validité CSS"><strong>CSS 2</strong></a>, <a href="http://www.ocawa.com/autotest/validate.php" title="Page testée par Ocawa, testez cette page sur le site ocawa"><strong>ADAE argent</strong> / <strong>WCAG 1.0 Niv3</strong></a>&nbsp;&nbsp;
        <br />[ exectime: <PLOOPI_EXEC_TIME> ms | php: <PLOOPI_PHP_P100>% | sql: <PLOOPI_NUMQUERIES>q | size: <PLOOPI_PAGE_SIZE>kB ]&nbsp;&nbsp;
    </div>
    </div>

</body>
</html>
